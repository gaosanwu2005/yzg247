(function (_this) {

    //加入购物车
    _this.addcart = function (num, gid) {
        $$.getJSON('/index.php/api/shop/addcart', {gid: $$(gid).val(),goods_num:$$(num).val(),spec_key_name:$$('#spec_key_name').val(),spec_key:$$('#spec_key').val()}, function (d) {
            console.log(d);
            myApp.alert(d.msg);
            if (d.url) {
                mainView.router.load({url: d.url});
            }
        })
    };

    //立即购买
    _this.buy = function (num, gid) {
        $$.getJSON('/index.php/api/shop/buy', {gid: $$(gid).val(),goods_num:$$(num).val(),spec_key_name:$$('#spec_key_name').val(),spec_key:$$('#spec_key').val()}, function (d) {
            console.log(d);
            myApp.alert(d.msg);
            if (d.url) {
                mainView.router.load({url: d.url});
            }
        })
    };

    //规格选择
    _this.spec = function (obj,spec_key_name,spec_key) {
        $$(obj).addClass("active").siblings().removeClass("active");
        $$(spec_key_name).val($$(obj)[0].innerText);
        $$(spec_key).val($$(obj)[0].dataset.value);
        $$('#price').html($$(obj)[0].dataset.value);

    };


    //删除购物车
    _this.delcart = function (gid) {
        $$.getJSON('/index.php/api/shop/delcart', {gid: gid}, function (d) {
            console.log(d);
            myApp.alert(d.msg);
            if (d.url) {
                mainView.router.load({url: d.url,reload:true});
            }
        })
    };

    //购物车加号
    _this.upcart = function (gid) {
        $$.getJSON('/index.php/api/shop/upcart', {gid: gid}, function (d) {
            if (d.url) {
                mainView.router.load({url: d.url,reload:true});
            }
        })
    };

    //购物车减号
    _this.downcart = function (gid) {
        $$.getJSON('/index.php/api/shop/downcart', {gid: gid}, function (d) {
            if (d.url) {
                mainView.router.load({url: d.url,reload:true});
            }
        })
    };

    //购物车商品选择
    _this.chosecart = function (gid) {
        console.log(gid);
        console.log($$(gid)[0].checked);
        console.log($$(gid)[0].value);
        if($$(gid)[0].checked){
            $$.getJSON('/index.php/api/shop/chosecart', {gid: $$(gid)[0].value,chose:1}, function (d) {
                if (d.url) {
                    mainView.router.load({url: d.url,reload:true});
                }
            })
        }else{
            $$.getJSON('/index.php/api/shop/chosecart', {gid: $$(gid)[0].value,chose:0}, function (d) {
                if (d.url) {
                    mainView.router.load({url: d.url,reload:true});
                }
            })
        }
    };


    //购物车店铺选择
    _this.choseshop = function (gid) {
        // console.log(gid);
        console.log($$(gid)[0].checked);
        console.log($$(gid)[0].value);
        if($$(gid)[0].checked){
            $$.getJSON('/index.php/api/shop/choseshop', {gid: $$(gid)[0].value,chose:1}, function (d) {
                if (d.url) {
                    mainView.router.load({url: d.url,reload:true});
                }
            })
        }else{
            $$.getJSON('/index.php/api/shop/choseshop', {gid: $$(gid)[0].value,chose:0}, function (d) {
                if (d.url) {
                    mainView.router.load({url: d.url,reload:true});
                }
            })
        }
    };

    // 结算
    _this.suancart = function (obj) {
        myApp.prompt('请输入二级密码',
            function (value) {
                jQuery.post("/api/user/seccodedo", {pass2: value}, function (d) {
                    console.log(d);
                    if (d.code == 0) {
                        myApp.alert(d.msg);
                        // mainView.router.back();
                    } else {
                        var formData = myApp.formToJSON(obj);
                        $$.getJSON('/index.php/api/shop/suancart', formData, function (d) {
                            myApp.alert(d.msg);
                            if (d.url) {
                                mainView.router.load({url: d.url});
                            }
                        })
                    }
                }, 'json');
            },
            function (value) {
                // mainView.router.back();
            }
        );
    };

    //商品详情页增加
    _this.up = function (obj) {
       var up= parseInt($$(obj).val())+1;
        $$(obj).val(up);
    };

    //商品详情页减少
    _this.down = function (obj) {
        var up= parseInt($$(obj).val());
        if(up>1){
            up= parseInt($$(obj).val())-1;
            $$(obj).val(up);
        }

    };

    //改变订单状态
    _this.c_order = function (order_id,state) {
        $$.getJSON('/index.php/api/shop/c_order', {gid: order_id,state:state}, function (d) {
            myApp.alert(d.msg);
            if(d.data=="321"){
                mainView.router.refreshPage();
            }
        })
    };

    //商品搜索
    _this.find = function (obj) {
        var url='/mobile/goods/goodslist/find/'+$$(obj).val();
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find2 = function (obj) {
        var url='/mobile/goods/goodslist/find/'+$$(obj)[0].innerText;
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find3 = function (id) {
        var url='/mobile/goods/goodslist2/find/'+id;
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find4 = function (id) {
        var url='/mobile/goods/goodslist3/find/'+id;
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find5 = function (id) {
        var url='/mobile/goods/goodslist5/find/'+id;
        mainView.router.load({url: url});
    }



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
        myApp.confirm('确定卖出' + num + '吗', function () {
            $$.getJSON('index.php/api/trade/clicksale', {id: id}, function (d) {
                console.log(d);
                myApp.alert(d.msg);
                if (d.url) {
                    mainView.router.load({url: d.url});
                }
                _this.fresh1();

            })
        })
    };

    // //点击购买
    // _this.buy = function (id, num) {
    //     myApp.confirm('确定购买' + num + '吗', function () {
    //         $$.getJSON('index.php/api/trade/clickbuy', {id: id}, function (d) {
    //             console.log(d);
    //             myApp.alert(d.msg);
    //             _this.fresh1();
    //
    //         })
    //     })
    // };

    //撤销卖单
    _this.cancelSaleOrder = function (id) {
        myApp.confirm('确定撤销吗', function () {
            $$.getJSON('index.php/api/trade/CancelSaleOrder', {sale: id}, function (d) {
                console.log(d);
                myApp.alert(d.msg);
                _this.fresh1();

            })
        })
    };

    //撤销买单
    _this.cancelBuyOrder = function (id) {
        myApp.confirm('确定撤销吗', function () {
            $$.getJSON('index.php/api/trade/cancelBuyOrder', {buy: id}, function (d) {
                console.log(d);
                myApp.alert(d.msg);
                _this.fresh1();

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
            mainView.router.load({url: 'index.php/api/Info/tdinfo'});
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

})(window.SHOP = {});