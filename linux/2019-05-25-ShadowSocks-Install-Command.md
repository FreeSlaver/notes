<div id="table-of-contents">
<h2>Table of Contents</h2>
<div id="text-table-of-contents">
<ul>
<li><a href="#sec-1">1. Shadowsocks最简最快安装步骤</a>
<ul>
<li><a href="#sec-1-1">1.1. 启动shadowsocks</a></li>
</ul>
</li>
</ul>
</div>
</div>

# Shadowsocks最简最快安装步骤<a id="sec-1" name="sec-1"></a>

yum install epel-release
yum update
yum install   python-setuptools m2crypto wget
wget <https://pypi.python.org/packages/source/p/pip/pip-1.3.1.tar.gz> &#x2013;no-check-certificate
tar -xzvf pip-1.3.1.tar.gz
cd pip-1.3.1
python setup.py install
easy<sub>install</sub> pip
pip install shadowsocks

## 启动shadowsocks<a id="sec-1-1" name="sec-1-1"></a>

sudo ssserver -p 端口号 -k 密码 -m rc4-md5  -d start
