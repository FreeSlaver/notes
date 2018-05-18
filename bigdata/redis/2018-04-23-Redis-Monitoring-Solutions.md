<div id="table-of-contents">
<h2>Table of Contents</h2>
<div id="text-table-of-contents">
<ul>
<li><a href="#sec-1">1. Reids监控解决方案</a>
<ul>
<li><a href="#sec-1-1">1.1. 监控之前</a></li>
<li><a href="#sec-1-2">1.2. 需监控的指标</a>
<ul>
<li><a href="#sec-1-2-1">1.2.1. 系统指标</a></li>
<li><a href="#sec-1-2-2">1.2.2. 可用性指标</a></li>
<li><a href="#sec-1-2-3">1.2.3. 性能指标</a></li>
<li><a href="#sec-1-2-4">1.2.4. 错误指标</a></li>
</ul>
</li>
<li><a href="#sec-1-3">1.3. INFO和MONITOR命令</a>
<ul>
<li><a href="#sec-1-3-1">1.3.1. INFO</a></li>
<li><a href="#sec-1-3-2">1.3.2. MONITOR</a></li>
</ul>
</li>
<li><a href="#sec-1-4">1.4. 可选方案</a>
<ul>
<li><a href="#sec-1-4-1">1.4.1. Redis-stat</a></li>
<li><a href="#sec-1-4-2">1.4.2. Redmon</a></li>
<li><a href="#sec-1-4-3">1.4.3. RedisLive</a></li>
<li><a href="#sec-1-4-4">1.4.4. Redis-Faina</a></li>
</ul>
</li>
<li><a href="#sec-1-5">1.5. 我们期望的效果</a></li>
<li><a href="#sec-1-6">1.6. 参考资料和扩展阅读</a></li>
</ul>
</li>
</ul>
</div>
</div>

# Reids监控解决方案<a id="sec-1" name="sec-1"></a>

## 监控之前<a id="sec-1-1" name="sec-1-1"></a>

在做Redis监控之前，我们先自我提问，思考一下，到底需要监控Redis的哪些东西？
这涉及到Redis在整个系统中所起到的作用，和使用到的一些特性。我们这里以Redis做缓存为例。

思考数据的流转过程，客户端向Redis中写入，更新数据，并读取出来。写入更新的速率如何，读取时是不是会很慢。
Redis中的KV会持久化到磁盘上，

那么监控的话，以下几点为关键：
1.  集群的健康情况，slave是否挂了，同步落后多少
2.  redis内存占用是否满了？发生了磁盘swap？
3.  查询耗时，是否有查询导致阻塞的情况，如用了keys
4.  缓存命中率如何，是否有大量Keys被驱逐
5.  是否有一些网络异常，系统异常抛出

## 需监控的指标<a id="sec-1-2" name="sec-1-2"></a>

### 系统指标<a id="sec-1-2-1" name="sec-1-2-1"></a>

常见的，通用的需要监控的指标有：进程存活，CPU，内存，磁盘等。
CPU占用过高只要不长时间保持就行，但是最好不要出现超过400%之类的情况。
内存如果被Redis占用满了，要么驱逐出KV，要么进行内存和磁盘页交换（page swap），会非常影响性能。

### 可用性指标<a id="sec-1-2-2" name="sec-1-2-2"></a>

connected<sub>clients：连到redis的客户端数目</sub>
rdb<sub>last</sub><sub>save</sub><sub>time：最后一次刷磁盘距当前时间，大于3600秒告警</sub>
connected<sub>slaves：连到此master的slave数量</sub>
master<sub>last</sub><sub>io</sub><sub>seconds</sub><sub>ago：距最后一次master和slave通信的时间，大于30秒告警</sub>

### 性能指标<a id="sec-1-2-3" name="sec-1-2-3"></a>

latency：redis查询瓶颈耗时，大于200ms告警
mem<sub>fragmentation</sub><sub>ratio：redis已使用内存大小</sub>/linux虚拟内存页大小，过高的比率
导致swap，降低性能。比值大于1.5告警

### 错误指标<a id="sec-1-2-4" name="sec-1-2-4"></a>

rejected<sub>connections：超过最大客户端数后，拒绝掉的连接数。</sub>
keyspace<sub>misses：查询key时失败的数量。大于0告警</sub>
master<sub>link</sub><sub>down</sub><sub>since</sub><sub>seconds：master和slave之间连接断掉的时间。大于60秒告警。</sub>

## INFO和MONITOR命令<a id="sec-1-3" name="sec-1-3"></a>

那么这些数据从哪里来了？
Redis为此只提供了2个相关的命令：INFO和MONITOR

### INFO<a id="sec-1-3-1" name="sec-1-3-1"></a>

使用INFO命令可以获取以下几个部分的信息：
server
clients
memory
persisitence
stats
replication
cpu
commandstats
cluster
keyspace
下面是个截图，可以看看大概的内容：

### MONITOR<a id="sec-1-3-2" name="sec-1-3-2"></a>

MONITOR命令可以回溯每一个被Redis服务器执行的命令，帮助理解在此期间发生了什么，
主要用于诊断Redis服务或数据错误时的故障原因，对性能有一定影响。

## 可选方案<a id="sec-1-4" name="sec-1-4"></a>

其实这些所有方案都是通过Redis自身提供的INFO和MONITOR2个命令，然后用脚本定时去
拉取相关的数据，然后连接起来，在时间轴上展示。

### Redis-stat<a id="sec-1-4-1" name="sec-1-4-1"></a>

使用Redis自身提供的Info命令，不影响性能，有Web UI界面。Ruby写的。
使用Redis的INFO命令，不影响性能，有Web UI界面但也可以命令行使用，Ruby写的。
可以展示CPU，内存使用，命令，缓存击中率，逾期和被驱逐的Key。


最大的缺陷是：不在维护了。

### Redmon<a id="sec-1-4-2" name="sec-1-4-2"></a>

也有Web页面，但展示的监控指标较少，除此之外还提供cli命令行，更新redis server配置等功能。

### RedisLive<a id="sec-1-4-3" name="sec-1-4-3"></a>

使用的Monitor命令，有Web界面。

### Redis-Faina<a id="sec-1-4-4" name="sec-1-4-4"></a>

使用的Redis的MONITOR命令，然后对执行的命令次数，耗时进行统计，
能够看到经常使用的命令，最重的操作，最慢的调用等。
下面是一张执行后输出的结果截图：

Python编写，无Web界面。
Github地址：<https://github.com/facebookarchive/redis-faina>

## 我们期望的效果<a id="sec-1-5" name="sec-1-5"></a>

在平时正常情况，使用INFO来显示Redis的状态，发生异常，故障后能主动使用MONITOR
输出近段时间内执行的所有命令，用于定位问题。

目前是没有同时支持INFO和MONITOR的监控程序的。比较好的解决方法是：
选择一个已经写好INFO或MONITOR的，然后完成另外一部分的功能。

## 参考资料和扩展阅读<a id="sec-1-6" name="sec-1-6"></a>

[How to Monitor Redis](https://blog.serverdensity.com/monitor-redis/)
