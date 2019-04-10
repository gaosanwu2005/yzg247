define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/order/index',
                    add_url: 'shop/order/add',
                    edit_url: 'shop/order/edit',
                    del_url: 'shop/order/del',
                    multi_url: 'shop/order/multi',
                    table: 'shop_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'order_id',
                sortName: 'order_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'order_id', title: __('Order_id')},
                        {field: 'order_sn', title: __('Order_sn')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'order_status', title: __('Order_status'), visible:false, searchList: {"0":__('Order_status 0'),"1":__('Order_status 1'),"2":__('Order_status 2'),"3":__('Order_status 3'),"4":__('Order_status 4'),"5":__('Order_status 5')}},
                        {field: 'order_status_text', title: __('Order_status'), operate:false},
                        {field: 'consignee', title: __('Consignee')},
                        // {field: 'country', title: __('Country')},
                        // {field: 'province', title: __('Province')},
                        // {field: 'city', title: __('City')},
                        // {field: 'district', title: __('District')},
                        // {field: 'twon', title: __('Twon')},
                        {field: 'address', title: __('Address')},
                        // {field: 'zipcode', title: __('Zipcode')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'pay_name', title: __('Pay_name')},
                        // {field: 'invoice_title', title: __('Invoice_title')},
                        {field: 'goods_price', title: __('Goods_price'), operate:'BETWEEN'},
                        // {field: 'shipping_price', title: __('Shipping_price'), operate:'BETWEEN'},
                        {field: 'ptype', title: __('Ptype')},
                        // {field: 'order_amount', title: __('Order_amount'), operate:'BETWEEN'},
                        {field: 'total_amount', title: __('Total_amount'), operate:'BETWEEN'},
                        {field: 'add_time', title: __('Add_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'shipping_time', title: __('Shipping_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'confirm_time', title: __('Confirm_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'order_prom_id', title: __('Order_prom_id')},
                        // {field: 'user_note', title: __('User_note')},
                        // {field: 'admin_note', title: __('Admin_note')},
                        {field: 'shop_id', title: __('Shop_id')},
                        {field: 'shipping_name', title: __('Shipping_name')},
                        {field: 'shipping_num', title: __('Shipping_num')},
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: '订单详情',
                                    text: '订单详情',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'shop/order/orderdetail',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), { title: "回传数据" });
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '编辑',
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'shop/order/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        },
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
            }
        }
    };
    return Controller;
});