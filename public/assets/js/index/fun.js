(function (_this) {

    //ajax
    _this.ajax = function (col, data) {
        myApp.showIndicator();
        $$.getJSON(col, data, function (e) {
            myApp.hideIndicator();
            if(e.data==321){
                mainView.router.refreshPage();
            }
            if(e.data==789){
                mainView.router.back();
            }

            if (e.msg) {
                myApp.alert(e.msg, function () {

                    if(document.getElementById("popover")){
                        console.log('bbbb');
                        myApp.closeModal('.popover');
                        document.getElementById("popover").style.display="none";
                    }

                    if (e.url !== '') {
                        mainView.router.load({url: e.url, pushState: true});
                    }
                });
            } else if (e.url !== '') {
                mainView.router.load({url: e.url, pushState: true});
            }
        })
    };



    //ajax
    _this.hongbaoajax = function (col, data) {
        myApp.showIndicator();
        $$.getJSON(col, data, function (e) {
            myApp.hideIndicator();
            if(e.data==321){
                mainView.router.refreshPage();
            }
            if(e.data==789){
                mainView.router.back();
            }

            if (e.msg) {
                myApp.confirm(e.msg, function () {
                    console.log("抢红包");

                    // if(document.getElementById("popover")){
                    //     console.log('bbbb');
                    //     myApp.closeModal('.popover');
                    //     document.getElementById("popover").style.display="none";
                    // }
                    //
                    // if (e.url !== '') {
                    //     mainView.router.load({url: e.url, pushState: true});
                    // }
                });
            } else if (e.url !== '') {
                mainView.router.load({url: e.url, pushState: true});
            }
        })
    };



    //backajax
    _this.backajax = function (col, data, callback) {
        myApp.showIndicator();
        $$.getJSON(col, data, function (data) {
            myApp.hideIndicator();
            if (data.msg) {
                myApp.alert(data.msg, function () {
                    callback(data);
                });
            } else if (data.url) {
                mainView.router.load({url: data.url, pushState: true, reload: true});
            }
        })
    };
    //ajax
    _this.ajaxform = function (col,form) {
        myApp.showIndicator();
        var storedData = myApp.formToJSON(form);
        $$.getJSON(col, storedData, function (data) {
            myApp.hideIndicator();
            if(data.data=="321"){
                mainView.router.refreshPage();
            }
            if (data.msg) {
                myApp.alert(data.msg, function () {

                    if(document.getElementById("popover")){
                        myApp.closeModal('.popover');
                        document.getElementById("popover").style.display="none";
                    }

                    if (data.url !== '') {
                        mainView.router.load({url: data.url, pushState: true, reload: true});
                    }
                });
            } else if (data.url !== '') {
                mainView.router.load({url: data.url, pushState: true});
            }
        })
    };

    // MUI拍照添加文件
    _this.appendByCamera = function(id) {

        plus.camera.getCamera().captureImage(function(p) {
            appendFile(p, id);
        });
    }
    // MUI从相册添加文件
    _this.appendByGallery = function(id) {

        plus.gallery.pick(function(p) {
            appendFile(p, id);
        });
    }

    function appendFile(p, id) {
        var files = [];
        plus.io.resolveLocalFileSystemURL(p, function(entry) {
            entry.getMetadata(function(metadata) {
                console.log(metadata.size/1000);
                //大于500kb 压缩
                if(metadata.size / 1000 > 500) {
                    //图片压缩
                    var zippath = "_doc/" + entry.name;
                    plus.zip.compressImage({
                            src: p,
                            dst: zippath,
                            overwrite: true,
                            width: "50%",
                            height: "50%"
                        },
                        function() {
                            console.log("Compress success!");
                            plus.io.resolveLocalFileSystemURL(zippath, function(e) {
                                e.getMetadata(function(metadata) {
                                    console.log(metadata.size/1000);
                                    var ul = e.toLocalURL();
                                    console.log(ul);
                                    var task = plus.uploader.createUpload('http://'+ window.location.host+'/api/user/realimg', {
                                            method: "POST"
                                        },
                                        function(t, status) {
                                            if(status == 200) {
                                                console.log("上传成功：");
                                                // console.log(JSON.stringify(t));
                                                // console.log(t.responseText.data);
                                                // console.log(JSON.parse(t.responseText));
                                                var obj = JSON.parse(t.responseText);
                                                var newurl =  obj.data;
                                                //更新数据信息
                                                var img = document.getElementById(id);
                                                var input=document.getElementById(id+'input');
                                                img.src = newurl;
                                                input.value = newurl;
                                            } else {
                                                console.log("上传失败");
                                                // console.log(JSON.stringify(t));
                                                // console.log(t.responseText);
                                                // console.log(JSON.parse((t.responseText)));
                                                // console.log(status);
                                            }
                                        }
                                    );
                                    task.addFile(ul, {
                                        key: id
                                    });
                                    console.log("开始上传：");
                                    task.start();

                                }, function() {
                                    console.log(e.message);
                                });
                            });
                        },
                        function(error) {
                            console.log(JSON.stringify(error));
                        });
                } else {
                    var fe = document.getElementById(id);
                    var ul = entry.toLocalURL();
                    fe.src = ul;
                    files.push({
                        name: id,
                        path: ul
                    });
                }
            }, function() {
                console.log(e.message);
            });
        });
    }

    //选择图片，马上预览
    _this.showimg =  function (obj,name) {
        var imgBase64 = '';     //存储图片的imgBase64
        var fileObj = obj.files[0];

        // 调用函数，对图片进行压缩
        MY.compress(fileObj,name,function(imgBase64){
            imgBase64 = imgBase64;    //存储转换的base64编码
            console.log("111");
            document.getElementById(name).setAttribute("src",imgBase64);
            // $$(name).attr('src',imgBase64); //显示预览图片
            // console.log($$(name).attr('src'));
        });

        console.log(obj);
        console.log(fileObj);
        console.log("file.size = " + fileObj.size);  //file.size 单位为byte

        var reader = new FileReader();

        //读取文件过程方法
        reader.onloadstart = function (e) {
            console.log("开始读取....");
        }
        reader.onprogress = function (e) {
            console.log("正在读取中....");
        }
        reader.onabort = function (e) {
            console.log("中断读取....");
        }
        reader.onerror = function (e) {
            console.log("读取异常....");
        }
        // reader.onload = function (e) {
        //     console.log("成功读取....");
        //
        //     var img = document.getElementById(name);
        //     img.src = e.target.result;
        //     //或者 img.src = this.result;  //e.target == this
        // }

        // reader.readAsDataURL(file)
    }


    // 不对图片进行压缩，直接转成base64
    _this.directTurnIntoBase64 = function(fileObj,callback){
        var r = new FileReader();
        // 转成base64
        r.onload = function(){
            //变成字符串
            imgBase64 = r.result;
            console.log(imgBase64);
            callback(imgBase64);
        }
        r.readAsDataURL(fileObj);    //转成Base64格式
    }

    // 对图片进行压缩
    _this.compress = function compress(fileObj,name, callback) {
        if ( typeof (FileReader) === 'undefined') {
            console.log("当前浏览器内核不支持base64图标压缩");
            //调用上传方式不压缩
            directTurnIntoBase64(fileObj,callback);
        } else {
            try {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var image = jQuery('<img/>');
                    console.log(image);
                    image.load(function (){
                        console.log("开始压缩");
                        square = 700,   //定义画布的大小，也就是图片压缩之后的像素
                            canvas = document.createElement('canvas'),
                            context = canvas.getContext('2d'),
                            imageWidth = 0,    //压缩图片的大小
                            imageHeight = 0,
                            offsetX = 0,
                            offsetY = 0,
                            data = '';

                        canvas.width = square;
                        canvas.height = square;
                        context.clearRect(0, 0, square, square);

                        if (this.width > this.height) {
                            imageWidth = Math.round(square * this.width / this.height);
                            imageHeight = square;
                            offsetX = - Math.round((imageWidth - square) / 2);
                        } else {
                            imageHeight = Math.round(square * this.height / this.width);
                            imageWidth = square;
                            offsetY = - Math.round((imageHeight - square) / 2);
                        }
                        context.drawImage(this, offsetX, offsetY, imageWidth, imageHeight);
                        var data = canvas.toDataURL('image/jpeg');
                        //压缩完成执行回调
                        console.log("压缩成功!");
                        callback(data);
                    });
                    image.attr('src', e.target.result);
                    // console.log(image.attr("src"));
                };


                reader.readAsDataURL(fileObj);
            }catch(e){
                console.log("压缩失败!");
                //调用直接上传方式  不压缩
                directTurnIntoBase64(fileObj,callback);
            }
        }
    }


    _this.qrcode = function(obj){
        var inputDom = $$(obj);
        var imgFile = inputDom[0].files;
        var oFile = imgFile[0];
        var oFReader = new FileReader();
        var rFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
        if (imgFile.length === 0) {
            return;
        }
        if (!rFilter.test(oFile.type)) {
            alert("选择正确的图片格式!");
            return;
        }
        oFReader.onload = function(oFREvent) {
            qrcode.decode(oFREvent.target.result);
            qrcode.callback = function(data) {
                console.log(data)
                data = data.trim();
                if(data){
                    var obj = {
                        type : 3,   // 3为文本对象
                        content : data
                    };
                    // 正则验证是否为网址
                    var reg = /^(http|https|HTTPS|HTTP):\/\//;
                    var d = data.slice(0,1) == '{';
                    if(reg.test(data)){
                        obj.type = 1;    // 1为url
                        obj.content = data;
                        // 验证是否为对象
                    }else if(d){
                        d = JSON.parse(data);
                        if(typeof d == 'object'){
                            obj.type = 2;       // 2 为对象
                            obj.content = d;
                        }
                    }
                    // 处理函数
                    if(obj.type == 1){
                        window.location.href = obj.content;
                    }else if(obj.type == 2){
                        if(obj.content.userid){
                            window.location.href = "{:U('Mobile/User/trans')}"+"?p="+obj.content.userid;
                        }else{
                            layer.open({
                                content:'没有此用户'
                            });
                        }

                    }else{
                        layer.open({
                            content: obj.content
                        });
                    }
                }else{
                    alert('没有内容');
                }
            };
        };
        oFReader.readAsDataURL(oFile);
    };

    //全局定时器
    _this.timeArr = [];
    //倒计时函数
    _this.countTime = function (el, htmll) {
        console.log(1);
        //清除定时器
        if (_this.timeArr.length > 10) {
            console.log(2);
            for (var i = 0; i < _this.timeArr.length; i++) {
                clearInterval(_this.timeArr[i]);
            }
        }
        //结束时间
        $$(el).each(function (k, v) {
            var count = $$(v).attr('data-time');
            var ppid = $$(v).attr('data-ppid');
            if (!count) {
                return false;
            }
            //当前时间
            var count = parseInt(count);
            var nowtime = new Date().getTime();
            nowtime = parseInt(nowtime / 1000);

            //时间差
            if (count - nowtime < 0) {

                $$(el).html(htmll);
                return false;
            }
            var timeC = count - nowtime;
            _this.timeArr.push(window.setInterval(function () {

                timeC--;
                var d = parseInt(timeC / 86400);
                var h = parseInt((timeC % 86400) / 3600);
                var m = parseInt(((timeC % 86400) % 3600) / 60);
                var s = parseInt(((timeC % 86400) % 3600) % 60);
                if (d <= 0 && h <= 0 && m <= 0 && s <= 0) {

                    $$(el).html(htmll);
                    if (_this.timeArr.length > 0) {
                        for (var i = 0; i < _this.timeArr.length; i++) {
                            clearInterval(_this.timeArr[i]);
                        }
                    }
                } else {
                    h = h.toString().length == 1 ? ("0" + h) : h;
                    m = m.toString().length == 1 ? ("0" + m) : m;
                    s = s.toString().length == 1 ? ("0" + s) : s;
                    $$(v).html(d + '&nbsp;天&nbsp;' + h + '&nbsp;小时&nbsp;' + m + '&nbsp;分钟&nbsp;' + s + '&nbsp;秒&nbsp;');
                }
            }, 1000));

        })

    };

    _this.timeArr1 = [];

    //倒计时函数 投诉用 type:1=虚拟币 2=互助
    _this.countTime1 = function (el, type) {
        //清除定时器
        if (_this.timeArr1.length > 10) {
            console.log(4);
            for (var i = 0; i < _this.timeArr1.length; i++) {
                clearInterval(_this.timeArr1[i]);
            }
        }
        //结束时间
        $$(el).each(function (k, v) {
            var count = $$(v).attr('data-time');
            var ppid = $$(v).attr('data-ppid');
            if (!count) {
                return false;
            }
            //当前时间
            var count = parseInt(count);
            var nowtime = new Date().getTime();
            nowtime = parseInt(nowtime / 1000);
            // console.log(count);
            // console.log(nowtime);
            // console.log(count-nowtime<0);
            //时间差
            if (count - nowtime < 0) {
                if (type == 1) {
                    var htmll = '<a style="width: 100px;float: right" class="button button-round" href="/index.php/index/Business/shamts?ppid=' + ppid + '" >投诉</a>';
                } else {
                    var htmll = '<a style="width: 100px;float: right" class="button button-round" href="/index.php/index/Business2/shamts?ppid=' + ppid + '" >投诉</a>';
                }
                $$(el).html(htmll);
                return false;
            }
            var timeC = count - nowtime;
            _this.timeArr1.push(window.setInterval(function () {

                timeC--;
                var d = parseInt(timeC / 86400);
                var h = parseInt((timeC % 86400) / 3600);
                var m = parseInt(((timeC % 86400) % 3600) / 60);
                var s = parseInt(((timeC % 86400) % 3600) % 60);
                if (d <= 0 && h <= 0 && m <= 0 && s <= 0) {
                    var htmll = '已完成'
                    $$(el).html(htmll);
                    if (_this.timeArr1.length > 0) {
                        for (var i = 0; i < _this.timeArr1.length; i++) {
                            clearInterval(_this.timeArr1[i]);
                        }
                    }
                } else {
                    h = h.toString().length == 1 ? ("0" + h) : h;
                    m = m.toString().length == 1 ? ("0" + m) : m;
                    s = s.toString().length == 1 ? ("0" + s) : s;
                    $$(v).html(d + '&nbsp;天&nbsp;' + h + '&nbsp;小时&nbsp;' + m + '&nbsp;分钟&nbsp;' + s + '&nbsp;秒&nbsp;');
                }
            }, 1000));

        })

    };
    //验证账户
    _this.yzAccount = function (col, data, el) {

        $$.getJSON(col, data, function (d) {
            if (d.code === 0) {
                myApp.alert(d.msg);
            } else {
                $$(el).html(d.msg);
            }

        })
    };

    //上传头像
    _this.upload = function (col, formid, el, img) {
        var form = new FormData(document.getElementById(formid));
        $$.post(col, form, function (d) {
            if (d.code === 0) {
                myApp.alert(d.msg);
            } else {
                $$(el).attr('src', d.data.url);
                $$(img).val(d.data.url);
            }
        }, 'json')
    };

    //上传头像并更新
    _this.upload2 = function (col, formid, el) {
        var form = new FormData(document.getElementById(formid));
        $$.post(col, form, function (d) {
            console.log(d);
            if (d.code === 0) {
                myApp.alert(d.msg);
            } else {
                $$(el).attr('src', d.data);
            }
        }, 'json')
    };

    //聊天发图
    _this.upload3 = function (col, formid, el) {
        var form = new FormData(document.getElementById(formid));
        $$.post(col, form, function (d) {
            console.log(d);
            if (d.code === 0) {
                myApp.alert(d.msg);
            } else {
                var myMessagebar = myApp.messagebar('.messagebar');

                myMessagebar.value("<img src=\""+d.data.url+"\" alt=\"img\">")
                // $$(el).append('<img src="'+d.data.url+'" id="avatar" alt="img">');
            }
        }, 'json')
    };

    //post form
    _this.formpost = function (col, formid) {
        var form = new FormData(document.getElementById(formid));
        $$.post(col, form, function (e) {
            if (e.msg) {
                myApp.alert(e.msg, function () {
                    if (e.url !== '') {
                        mainView.router.load({url: e.url, pushState: true});
                    }
                    if(e.data==321){
                        mainView.router.refreshPage();
                    }
                    if(e.data==789){
                        mainView.router.back();
                    }
                });
            } else if (e.url !== '') {
                mainView.router.load({url: e.url, pushState: true});
            }
        }, 'json')
    };

    //计算手续费
    _this.suanfee = function (col, data, max, trade, fee, btn) {
        if (data.number % 1 != 0) {
            myApp.alert('数量请输入整数');
            $$(btn).addClass('disabled');
        } else {
            $$(btn).removeClass('disabled');
            $$.getJSON(col, data, function (d) {
                if (d.code === 0) {
                    myApp.alert(d.msg);
                    $$(btn).addClass('disabled');
                } else {
                    $$(btn).removeClass('disabled');
                    $$(max).html(d.max);
                    $$(trade).html(d.trade);
                    $$(fee).val(d.fee);
                }

            })
        }
    };

    //计算充值
    _this.suaneth = function (usd, eth, hui) {

        var pp = $$(usd).val() / hui;
        $$(eth).val(pp.toFixed(6));
    };

    //计算合计
    _this.sum = function (p1, p2, total) {
        var a=$$(p1).val()==''?0: parseFloat($$(p1).val());
        var b=$$(p2).val()==''?0: parseFloat($$(p2).val());
        var pp = a + b;

        $$(total).val(pp.toFixed(2));
    };

    //自动填写
    _this.same = function (p1, p2) {
        $$(p2).val($$(p1).val());
    };

    //复制网址
    _this.copywww = function (message) {

        var input = document.createElement("input");
        input.value = message;
        document.body.appendChild(input);
        input.select();
        input.setSelectionRange(0, input.value.length), document.execCommand('Copy');
        document.body.removeChild(input);
        myApp.alert("复制成功");

    };
    _this.fresh1 = function () {
        $$.getJSON("http://" + window.location.host + "/index.php/api/trade/hangqing", function (d) {
            console.log(d);
            var buytpl = $('script#buytpl').html();
            var compile1 = Template7.compile(buytpl);
            var html = compile1(d);
            $$('#buylist').html('');
            $$('#buylist').append(html);

            var saletpl = $('script#saletpl').html();
            var compile2 = Template7.compile(saletpl);
            var html2 = compile2(d);
            $$('#salelist').html('');
            $$('#salelist').append(html2);

            var ordertpl = $('script#ordertpl').html();
            var compile3 = Template7.compile(ordertpl);
            var html3 = compile3(d);
            $$('#orderlist').html('');
            $$('#orderlist').append(html3);

            var historytpl = $('script#historytpl').html();
            var compile4 = Template7.compile(historytpl);
            var html4 = compile4(d);
            $$('#history').html('');
            $$('#history').append(html4);
            //绑定倒计时函数
            MY.countTime('.countTime');
        })
    };

    //点击卖出
    _this.sale = function (id, num) {

        myApp.prompt('请输入二级密码',
            function (value) {
                jQuery.post("/api/user/seccodedo", {pass2: value}, function (d) {
                    console.log(d);
                    if (d.code == 0) {
                        myApp.alert(d.msg);
                        // mainView.router.back();
                    } else {
                        myApp.confirm('确定卖出' + num + '吗', function () {
                            $$.getJSON('/index.php/api/trade/clicksale', {id: id}, function (d) {
                                console.log(d);
                                myApp.alert(d.msg);
                                if (d.url) {
                                    mainView.router.load({url: d.url});
                                }
                                if(d.data=="321"){
                                    mainView.router.refreshPage();
                                }

                            })
                        })
                    }
                }, 'json');
            },
            function (value) {
                // mainView.router.back();
            }
        );

    };

    //点击购买
    _this.buy = function (id, num) {
        myApp.confirm('确定购买' + num + '吗', function () {
            $$.getJSON('/index.php/api/trade/clickbuy', {id: id}, function (d) {
                console.log(d);
                myApp.alert(d.msg);
                if(d.data=="321"){
                    mainView.router.refreshPage();
                }

            })
        })
    };

    //撤销卖单
    _this.cancelSaleOrder = function (id) {
        myApp.confirm('确定撤销吗', function () {
            $$.getJSON('/index.php/api/trade/CancelSaleOrder', {sale: id}, function (d) {
                console.log(d);
                myApp.alert(d.msg);
                if(d.data=="321"){
                    mainView.router.refreshPage();
                }

            })
        })
    };

    //撤销买单
    _this.cancelBuyOrder = function (id) {
        myApp.confirm('确定撤销吗', function () {
            $$.getJSON('/index.php/api/trade/cancelBuyOrder', {buy: id}, function (d) {
                console.log(d);
                myApp.alert(d.msg);
                if(d.data=="321"){
                    mainView.router.refreshPage();
                }

            })
        })
    };

    //写cookies

    _this.setCookie = function (name, value) {
        var Days = 30;
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
    };

//读取cookies
    _this.getCookie = function (name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");

        if (arr = document.cookie.match(reg))

            return unescape(arr[2]);
        else
            return null;
    };

//删除cookies
    _this.delCookie = function (name) {
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval = _this.getCookie(name);
        if (cval != null)
            document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
    };

    //买单ajax
    _this.buyajax = function () {
        // 上次加载的序号
        var lastIndex = $$('#buyajax li').length;
        // 每次加载添加多少条目
        var itemsPerLoad = 5;
        var begin = lastIndex + 11;
        var end = itemsPerLoad;
        $$.getJSON("/index.php/api/trade/buyajax/from/" + begin + "/to/" + end, function (d) {
            console.log(d);
            console.log(JSON.stringify(d.buy) === '[]');
            if (JSON.stringify(d.buy) === '[]') {
                // 加载完毕，则注销无限加载事件，以防不必要的加载
                myApp.alert('加载完毕');
            } else {
                var historytpl = $('script#buytpl').html();
                var compile4 = Template7.compile(historytpl);
                var html = compile4(d);
                // 添加新条目
                $$('#buyajax').append(html);
            }

        });
    };

    //卖单ajax
    _this.saleajax = function () {
        // 上次加载的序号
        var lastIndex = $$('#saleajax li').length;
        // 每次加载添加多少条目
        var itemsPerLoad = 5;
        var begin = lastIndex + 11;
        var end = itemsPerLoad;
        $$.getJSON("/index.php/api/trade/saleajax/from/" + begin + "/to/" + end, function (d) {
            console.log(d);
            console.log(JSON.stringify(d.sale) === '[]');
            if (JSON.stringify(d.sale) === '[]') {
                // 加载完毕，则注销无限加载事件，以防不必要的加载
                myApp.alert('加载完毕');
            } else {
                var historytpl = $('script#saletpl').html();
                var compile4 = Template7.compile(historytpl);
                var html = compile4(d);
                // 添加新条目
                $$('#saleajax').append(html);
            }

        });
    };

    //检查等级
    _this.checklevel = function (n) {
        if (n > 1) {
            mainView.router.load({url: '/index.php/api/Info/tdinfo'});
        } else {
            myApp.alert('您的级别不足，暂时不能创建');
        }
    }

    //检查等级
    _this.findcoin = function () {
        var bian = $$('#bian').val();
        $$.getJSON("index.php/api/Info/mytree/bian/" + bian, function (d) {
            if (d.state == 1) {
                var historytpl = $('script#cointpl').html();
                var compile4 = Template7.compile(historytpl);
                var html = compile4(d);
                // 添加新条目
                $$('#treeDemo2').html('');
                $$('#treeDemo2').append(html);
            }
            myApp.alert(d.msg);
        });
    }

    _this.chosebuy = function (obj,feth) {
        console.log($$(obj)[0].innerText)
        $$.get("/index.php/index/trade2/chosebuyajax/price/" + $$(obj)[0].innerText, function (d) {
            console.log(d)
            $$(feth).html('');
            $$(feth).html(d);
        });
    }
    _this.chosesale = function (obj,feth) {
        console.log($$(obj)[0].innerText)
        $$.get("/index.php/index/trade2/chosesaleajax/price/" + $$(obj)[0].innerText, function (d) {
            console.log(d)
            $$(feth).html('');
            $$(feth).html(d);
        });
    }

    _this.searchbuy = function (obj,feth) {
        console.log($$(obj).val())
        $$.get("/index.php/index/trade/buyajax/price/" + $$(obj).val(), function (d) {
            console.log(d)
            $$(feth).html('');
            $$(feth).html(d);
        });
    }
    _this.searchsale = function (obj,feth) {
        console.log($$(obj).val())
        $$.get("/index.php/index/trade/saleajax/price/" + $$(obj).val(), function (d) {
            console.log(d)
            $$(feth).html('');
            $$(feth).html(d);
        });
    }
    //提现手续费计算
    _this.fee = function (obj,rate,fe,rec,wall,btn) {
        if($$(obj).val()>0){
            if(parseFloat($$(wall).html())<$$(obj).val()){
                myApp.alert('余额不足');
                $$(btn).addClass('disabled');
                return false;
            }else{
                $$(btn).removeClass('disabled');
            }
            var fee =   $$(obj).val()*rate*0.01;
            $$(fe).val(fee.toFixed(4));
            var re=$$(obj).val()-fee.toFixed(4);
            $$(rec).html(re.toFixed(4));
        }else{
            myApp.alert('输入有误，请重新输入');
        }
    }
//全部提现
    _this.all = function (obj,to,rate) {

        $$(to).val($$(obj).html());
        MY.fee('#number',rate,'#fee','#received','#wall','#up')
    }

    //兑换手续费计算
    _this.btc2lmcfee = function (obj,rate,fe,rec,wall,btn,hl) {
        if($$(obj).val()>0){
            if(parseFloat($$(wall).html())<$$(obj).val()){
                myApp.alert('余额不足');
                $$(btn).addClass('disabled');
                return false;
            }else{
                $$(btn).removeClass('disabled');
            }
            var fee = $$(obj).val()*rate*0.01;
            $$(fe).val(fee.toFixed(4));
            var re=($$(obj).val()-fee.toFixed(4))*hl;
            $$(rec).html(re.toFixed(4));
        }else{
            myApp.alert('输入有误，请重新输入');
        }
    }
    _this.btc2lmcall = function (obj,to,rate,hl) {

        $$(to).val($$(obj).html());
        MY.btc2lmcfee('#number',rate,'#fee','#received','#wall','#up',hl)
    }
    //兑换手续费计算
    _this.lmc2btcfee = function (obj,rate,fe,rec,wall,btn,hl) {
        if($$(obj).val()>0){
            if(parseFloat($$(wall).html())<$$(obj).val()){
                myApp.alert('余额不足');
                $$(btn).addClass('disabled');
                return false;
            }else{
                $$(btn).removeClass('disabled');
            }
            var fee = $$(obj).val()*rate*0.01;
            $$(fe).val(fee.toFixed(4));
            var re=($$(obj).val()-fee.toFixed(4))/hl;
            $$(rec).html(re.toFixed(4));
        }else{
            myApp.alert('输入有误，请重新输入');
        }
    }
    _this.lmc2btcall = function (obj,to,rate,hl) {

        $$(to).val($$(obj).html());
        MY.lmc2btcfee('#number1',rate,'#fee1','#received1','#wall1','#up1',hl)
    }

    //级联效果
    _this.union = function (col, data, el) {

        $$.getJSON(col, data, function (d) {
            if (d.code === 0) {
                myApp.alert(d.msg);
            } else {
                var address = d.category;
                var option = '';
                for(var i=0;i<address.length;i++){  //循环获取返回值，并组装成html代码
                    option +='<option value='+address[i]['name']+ '>'+address[i]['name']+'</option>';
                }
                $$(el).html(option);
            }
        })
    };

    //隐藏显示
    _this.btnChange = function (obj) {
        if($$(obj).val()== '中' || $$(obj).val()== '高'){
            document.getElementById('highlight').style.display = 'none';
            document.getElementById('tip').style.display = 'none';
            document.getElementById('application').style.display = '';
            document.getElementById('commitment').style.display = '';
            document.getElementById('appdown').style.display = '';
            document.getElementById('commdown').style.display = '';
        } else if($$(obj).val()== '高技' || $$(obj).val()== '一级') {
            document.getElementById('highlight').style.display = '';
            document.getElementById('tip').style.display = '';
            document.getElementById('application').style.display = 'none';
            document.getElementById('commitment').style.display = 'none';
            document.getElementById('appdown').style.display = 'none';
            document.getElementById('commdown').style.display = 'none';
        }
    };

    //系统根据身份证号码获取出生年月
    _this.extractionBirthday = function (obj, name) {
        var tmpStr = "";

        var iIdNo = $$(obj).val().replace(/^\s+|\s+$/g, "");

        if ((iIdNo.length != 15) && (iIdNo.length != 18)) {
            alert('输入的身份证号位数错误');
            $$(obj).focus();
        }

        if (iIdNo.length == 15) {
            tmpStr = iIdNo.substring(6, 12);
            tmpStr = "19" + tmpStr;
            tmpStr = tmpStr.substring(0, 4) + "-" + tmpStr.substring(4, 6) + "-" + tmpStr.substring(6);
            document.getElementById(name).value = tmpStr;
        } else {
            tmpStr = iIdNo.substring(6, 14);
            tmpStr = tmpStr.substring(0, 4) + "-" + tmpStr.substring(4, 6) + "-" + tmpStr.substring(6);
            document.getElementById(name).value = tmpStr;
        }
    }

    //用iconfont修改checkbox样式
    _this.showiconfont = function (obj) {
        if ($$(obj).prop('checked') == true) {
            $$(obj).siblings('label').addClass('redchecked');
            $('#nextsub').removeAttr("disabled");
            $('#nextsub').attr('style', 'background: #4c9cff;');
        } else {
            $$(obj).siblings('label').removeClass('redchecked');
            $('#nextsub').attr('disabled',"true");
            $('#nextsub').attr('style', 'background: #c0c0c0;');
        }
    }

})(window.MY = {});