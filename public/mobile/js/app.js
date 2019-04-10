
/* 列表 */
function list(dialog) {
    
}
/* 购物车 */
function cart(dialog) {
    var dialog = YDUI.dialog;
    // 购物车加法
    $('.scroll-box').on('click','.add',function () {
        var $id = $(this).parents('.shop-item').attr('data-id');
        var $p = $(this).parents('.shop-item');
        if(!$id) return;
        app.ajax('','get',{type:'add',id:$id},function (data,dialog) {
            if(data.status){
                $p.find('num').text($p.find('num').text()*1+1);
                settlement();
            }
            if(data.info){
             dialog.toast(data.info,'none',1000);
            }
        });
    });
    // 购物车减法
    $('.scroll-box').on('click','.pre',function () {
        var $id = $(this).parents('.shop-item').attr('data-id');
        var $p = $(this).parents('.shop-item');
        if(!$id) return;
        app.ajax('','get',{type:'pre',id:$id},function (data,dialog) {
            if(data.status){
                $p.find('num').text($p.find('num').text()*1-1);
                settlement();
            }
            if(data.info){
                dialog.toast(data.info,'none',1000);
            }
        });
    });
    // 删除购物车
    $('.scroll-box').on('click','.delet',function () {
        var $id = $(this).parents('.shop-item').attr('data-id');
        var $p = $(this).parents('.shop-item');
        if(!$id) return;
        dialog.confirm('删除商品', '确定要删除此商品吗？', [
            {
                txt: '取消',
                color: false,
                callback: function () {}
            },
            {
                txt: '删除',
                color: "red",
                callback: function () {
                    app.ajax('','get',{id:$id},function (data) {
                        if(data.status){
                            $p[0].remove();
                            settlement();
                        }
                        if(data.info){
                            dialog.toast(data.info,'none',1000);
                        }
                    })
                }
            }
        ]);
    });
    // 提交订单
    $('.submit-btn').on('click',function () {
        var $len = $('.shop-item').length;
        if($len <= 0) return;
        dialog.confirm('订单提交','确定要购买购物车中的商品吗？',[
            {
                txt: '取消',
                color: false,
                callback: function () {}
            },
            {
                txt: '提交',
                color: "red",
                callback: function () {
                    app.ajax('','get',{},function (data) {
                        if(data.info){
                            dialog.toast(data.info,'none',1000,function () {
                                if(data.url){
                                    location.href = data.url;
                                }
                            });
                        }
                        else{
                            if(data.url){
                                location.href = data.url;
                            }
                        }
                    })
                }
            }
        ])
    });
    // 结算价格
    function settlement() {
        var prices = 0;
        $('.shop-item').each(function (k,v) {
            var price = $(v).find('.text-detail .centent span:first').text().substr(1)*1;
            var num = $(v).find('.num').text();
            prices += (price * num);
        });
        $('.prices em').text(prices.toFixed(2));
    }
    
}
/* 用户页面 */
function user(dialog) {
    // 返回上一页
    $('.nac-top .back').click(function () {
        history.back();
    })
}
/* 商品详情 */
function goods(dialog) {
    var $this = this;
    // 轮播图
    $('#J_Slider').slider({
        speed: 200,
        autoplay: 3000,
        lazyLoad: true
    });
    // 监听当前页面
    $('.points span:last').text($('.slider-pagination-item').length);
    var $ps =$('.points span:first');
    var poins = $('.slider-pagination-item');
    $('.slider-pagination').css('display','none');
    var timer = setInterval(function () {
        poins.each(function (k,v) {
            if($(v).hasClass('slider-pagination-item-active')){
                $ps.text(k+1);
            }
        })
    },100);
    // 查看详情
    $('.top-btn').on('click','.open-win',function () {
        $(this).removeClass('open-win').addClass('close-win').text('关闭详情');
        $('.detail-box').css({
            'height':'unset',
            'padding':'10px'
        });
    });
    // 关闭详情
    $('.top-btn').on('click','.close-win',function () {
        $(this).removeClass('close-win').addClass('open-win').text('查看详情');
        $('.detail-box').css({
            'height':'0',
            "padding":'0'
        });
    });
    // 加入购物车
    $('.addCart').click(function () {
        $('.spec-mark').css('display','flex');
    });
    // 立即购买
    $('.buy').click(function () {
        $('.spec-mark').css('display','flex');
    });
    // 关闭规格选项
    $('.spec-mark').on('click',function (e) {
        // 阻止冒泡
        if(e.stopPropagation){
            e.stopPropagation();
        }else{
            e.cancelBubble = true;
        }
        $(this).css('display','none');
    });
    $('.spec-box').click(function (e) {
        e.stopPropagation();
    });
    // 选择规格
    $('.spec-mark .item-box').on('click','span',function () {
        $(this).parents('.item-box').find('span').removeClass('on');
        $(this).addClass('on');
    });
    // 增加数量
    $('.input-row').on('click','.add',function () {
        var $inp = $(this).parents('.right').find('.num');
        var num = $inp.text()*1 +1;
        if(num > 99) {
            dialog.toast('最多允许购买99件','none',1000);
            return false;
        }
        $inp.text(num);
    });
    // 减少数量
    $('.input-row').on('click','.pre',function () {
        var $inp = $(this).parents('.right').find('.num');
        var num = $inp.text()*1 -1;
        if(num <= 0) {
            dialog.toast('最少不能低于1件','none',1000);
            return false;
        }
        $inp.text(num);
    });
    // 提交规格
    $('.btns').click(function () {
        var $u = $(this).attr('data-url');
        var $id = $(this).attr('data-id');
        var $type = $(this).attr('data-type');
        var $num = $('.input-row .num').text();
        var obj = {};
        obj.id = $id;
        obj.type = $type;
        obj.spec = [];
        obj.num = $num;
        // 获取规格
        $('.item-box').each(function (k,v) {
            var $s = $(v).find('span');
            for(var i = 0 ; i <$s.length ; i++){
                if($s.eq(i).hasClass('on')){
                    var id = $s.eq(i).attr('data-id');
                    obj.spec.push(id);
                    break;
                }
            }
        });
        // 提交
        $this.ajax($u,'post',obj);
    })
}
/* 充值提现 */
function recharge(dialog) {
    //  全部提现
    $('.put-btn').on('click',function () {
        console.log(1)
        var $p = $('.put-info em').text();
        $('.recharge-row input[type=number]').val($p);
    });
    // 提交充值提现
    app.bindForm('.ajax-submit');
}
/* 申请代理 */
function agent(dialog) {
    // 选择代理商级别
    $(".agent-item").on('click',function () {
        var $this = $(this);
        $('.agent-item').removeClass('on');
        $this.addClass('on');
    });
}











