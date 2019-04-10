define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/goods/index',
                    add_url: 'shop/goods/add',
                    edit_url: 'shop/goods/edit',
                    del_url: 'shop/goods/del',
                    multi_url: 'shop/goods/multi',
                    table: 'shop_goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_id',
                sortName: 'goods_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'category_id', title: __('Category_id')},
                        {field: 'goods_sn', title: __('Goods_sn')},
                        {field: 'goods_name', title: __('Goods_name')},
                        {field: 'click_count', title: __('Click_count')},
                        {field: 'store_count', title: __('Store_count')},
                        {field: 'comment_count', title: __('Comment_count')},
                        {field: 'weight', title: __('Weight')},
                        {field: 'market_price', title: __('Market_price'), operate:'BETWEEN'},
                        {field: 'shop_price', title: __('Shop_price'), operate:'BETWEEN'},
                        {field: 'cost_price', title: __('Cost_price'), operate:'BETWEEN'},
                        {field: 'keywords', title: __('Keywords')},
                        {field: 'goods_remark', title: __('Goods_remark')},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        // {field: 'images', title: __('Images'), formatter: Table.api.formatter.images},
                        {field: 'is_real', title: __('Is_real'), visible:false, searchList: {"0":__('Is_real 0'),"1":__('Is_real 1')}},
                        {field: 'is_real_text', title: __('Is_real'), operate:false, formatter: Controller.api.formatter.real},
                        {field: 'is_on_sale', title: __('Is_on_sale'), visible:false, searchList: {"0":__('Is_on_sale 0'),"1":__('Is_on_sale 1')}},
                        {field: 'is_on_sale_text', title: __('Is_on_sale'), operate:false, formatter: Controller.api.formatter.on_sale},
                        {field: 'is_free_shipping', title: __('Is_free_shipping'), visible:false, searchList: {"0":__('Is_free_shipping 0'),"1":__('Is_free_shipping 1')}},
                        {field: 'is_free_shipping_text', title: __('Is_free_shipping'), operate:false, formatter: Controller.api.formatter.free_shipping},
                        {field: 'is_recommend', title: __('Is_recommend'), visible:false, searchList: {"0":__('Is_recommend 0'),"1":__('Is_recommend 1')}},
                        {field: 'is_recommend_text', title: __('Is_recommend'), operate:false, formatter: Controller.api.formatter.recommend},
                        {field: 'is_new', title: __('Is_new'), visible:false, searchList: {"0":__('Is_new 0'),"1":__('Is_new 1')}},
                        {field: 'is_new_text', title: __('Is_new'), operate:false, formatter: Controller.api.formatter.new},
                        {field: 'is_hot', title: __('Is_hot'), visible:false, searchList: {"0":__('Is_hot 0'),"1":__('Is_hot 1')}},
                        {field: 'is_hot_text', title: __('Is_hot'), operate:false, formatter: Controller.api.formatter.hot},
                        {field: 'on_time', title: __('On_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'sort', title: __('Sort')},
                        {field: 'last_update', title: __('Last_update')},
                        {field: 'spec_type', title: __('Spec_type')},
                        {field: 'give_integral', title: __('Give_integral')},
                        {field: 'exchange_integral', title: __('Exchange_integral')},
                        {field: 'sales_sum', title: __('Sales_sum')},
                        {field: 'prom_type', title: __('Prom_type'), visible:false, searchList: {"0":__('Prom_type 0'),"1":__('Prom_type 1'),"2":__('Prom_type 2'),"3":__('Prom_type 3')}},
                        {field: 'prom_type_text', title: __('Prom_type'), operate:false},
                        {field: 'spu', title: __('Spu')},
                        {field: 'sku', title: __('Sku')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {//渲染的方法
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
                ip: function (value, row, index) {
                    return '<a class="btn btn-xs btn-ip bg-success"><i class="fa fa-map-marker"></i> ' + value + '</a>';
                },
                browser: function (value, row, index) {
                    //这里我们直接使用row的数据
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                },
                hot: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change" data-id="' + row.goods_id+'" data-action="is_hot" data-params="'+(value == '否' ? '1' : '0')+'"><i class="fa ' + (value == '否' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                new: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change" data-id="' + row.goods_id+'" data-action="is_new" data-params="'+(value == '否' ? '1' : '0')+'"><i class="fa ' + (value == '否' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                recommend: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change" data-id="' + row.goods_id+'" data-action="is_recommend" data-params="'+(value == '否' ? '1' : '0')+'"><i class="fa ' + (value == '否' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                real: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change" data-id="' + row.goods_id+'" data-action="is_real" data-params="'+(value == '否' ? '1' : '0')+'"><i class="fa ' + (value == '否' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                on_sale: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change" data-id="' + row.goods_id+'" data-action="is_on_sale" data-params="'+(value == '否' ? '1' : '0')+'"><i class="fa ' + (value == '否' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },
                free_shipping: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change" data-id="' + row.goods_id+'" data-action="is_free_shipping" data-params="'+(value == '否' ? '1' : '0')+'"><i class="fa ' + (value == '否' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },

            },
        }
    };
    return Controller;
});