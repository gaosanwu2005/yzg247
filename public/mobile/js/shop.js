(function (_this) {

    //加入购物车
    _this.addcart = function (num, gid) {
        $$.getJSON('/addons/shop/ajax/addcart', {gid: $$(gid).val(),goods_num:$$(num).val(),spec_key_name:$$('#spec_key_name').val(),spec_key:$$('#spec_key').val()}, function (d) {
            console.log(d);
            myApp.alert(d.msg);
            if (d.url) {
                mainView.router.load({url: d.url});
            }
        })
    };

    //立即购买
    _this.buy = function (num, gid) {
        $$.getJSON('/addons/shop/ajax/buy', {gid: $$(gid).val(),goods_num:$$(num).val(),spec_key_name:$$('#spec_key_name').val(),spec_key:$$('#spec_key').val()}, function (d) {
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
        $$.getJSON('/addons/shop/ajax/delcart', {gid: gid}, function (d) {
            console.log(d);
            myApp.alert(d.msg);
            if (d.url) {
                mainView.router.load({url: d.url,reload:true});
            }
        })
    };

    //购物车加号
    _this.upcart = function (gid) {
        $$.getJSON('/addons/shop/ajax/upcart', {gid: gid}, function (d) {
            if (d.url) {
                mainView.router.load({url: d.url,reload:true});
            }
        })
    };

    //购物车减号
    _this.downcart = function (gid) {
        $$.getJSON('/addons/shop/ajax/downcart', {gid: gid}, function (d) {
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
            $$.getJSON('/addons/shop/ajax/chosecart', {gid: $$(gid)[0].value,chose:1}, function (d) {
                if (d.url) {
                    mainView.router.load({url: d.url,reload:true});
                }
            })
        }else{
            $$.getJSON('/addons/shop/ajax/chosecart', {gid: $$(gid)[0].value,chose:0}, function (d) {
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
            $$.getJSON('/addons/shop/ajax/choseshop', {gid: $$(gid)[0].value,chose:1}, function (d) {
                if (d.url) {
                    mainView.router.load({url: d.url,reload:true});
                }
            })
        }else{
            $$.getJSON('/addons/shop/ajax/choseshop', {gid: $$(gid)[0].value,chose:0}, function (d) {
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
                jQuery.post("/addons/shop/ajax/seccodedo", {pass2: value}, function (d) {
                    console.log(d);
                    if (d.code == 0) {
                        myApp.alert(d.msg);
                        // mainView.router.back();
                    } else {
                        var formData = myApp.formToJSON(obj);
                        $$.getJSON('/addons/shop/ajax/suancart', formData, function (d) {
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
        $$.getJSON('/addons/shop/ajax/c_order', {gid: order_id,state:state}, function (d) {
            myApp.alert(d.msg);
            if(d.data=="321"){
                mainView.router.refreshPage();
            }
        })
    };

    //商品搜索
    _this.find = function (obj) {
        var url='/addons/shop/goods/goodslist/find/'+$$(obj).val();
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find2 = function (obj) {
        var url='/addons/shop/goods/goodslist/find/'+$$(obj)[0].innerText;
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find3 = function (id) {
        var url='/addons/shop/goods/goodslist2/find/'+id;
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find4 = function (id) {
        var url='/addons/shop/goods/goodslist3/find/'+id;
        mainView.router.load({url: url});
    }
    //商品搜索
    _this.find5 = function (id) {
        var url='/addons/shop/goods/goodslist5/find/'+id;
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
})(window.SHOP = {});