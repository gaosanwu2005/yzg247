define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/order/index',
                    add_url: 'goods/order/add',
                    edit_url: 'goods/order/edit',
                    del_url: 'goods/order/del',
                    multi_url: 'goods/order/multi',
                    table: 'order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'uid', title: __('Uid')},
                        {field: 'account', title: __('Account')},
                        {field: 'gid', title: __('Gid')},
                        {field: 'gname', title: __('Gname')},
                        {field: 'sybl', title: __('Sybl'), operate:'BETWEEN'},
                        {field: 'kjsl', title: __('Kjsl')},
                        {field: 'yxzq', title: __('Yxzq')},
                        {field: 'gettime', title: __('Gettime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'yfprice', title: __('Yfprice'), operate:'BETWEEN'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'runday', title: __('Runday')},
                        {field: 'order_sn', title: __('Order_sn')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'iszs', title: __('Iszs'), visible:false, searchList: {"0":__('Iszs 0'),"1":__('Iszs 1')}},
                        {field: 'iszs_text', title: __('Iszs'), operate:false},
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
            }
        }
    };
    return Controller;
});