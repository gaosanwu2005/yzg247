define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'charge/third/index',
                    add_url: 'charge/third/add',
                    edit_url: 'charge/third/edit',
                    del_url: 'charge/third/del',
                    multi_url: 'charge/third/multi',
                    table: 'recharge_third',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'city', title: '城市'},
                        {field: 'institutions', title: '缴费公司'},
                        {field: 'usersn', title: '用户燃气号'},
                        {field: 'username', title: __('Username')},
                        {field: 'oil_account_number', title: __('Oil_account_number')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'order_sn', title: __('Order_sn')},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'payment', title: __('Payment')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}},
                        {field: 'num', title: __('Num')},
                        {field: 'lmcprice', title: __('Lmcprice')},
                        {field: 'type', title: __('Type')},
                        // {field: 'remarks', title: __('Remarks')},
                        {field: 'status_text', title: __('Status'), operate:false},
                        //自定义栏位,custom是不存在的字段
                        {field: 'custom', title: '审核', operate: false, formatter: Controller.api.formatter.jh},
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
                jh: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    if(row.status==0){
                        return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/pcharge" data-id="' + row.order_id+'" data-action="is_new" data-params="1">同意</a>'+'<a class="btn btn-xs btn-ip btn-change bg-danner" data-url="ajax/pcharge" data-id="' + row.order_id+'" data-action="is_new" data-params="2">拒绝</a>';

                    }
                },


            },
        }
    };
    return Controller;
});