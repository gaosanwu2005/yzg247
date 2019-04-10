define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trade/tgmx/index',
                    add_url: 'trade/tgmx/add',
                    edit_url: 'trade/tgmx/edit',
                    del_url: 'trade/tgmx/del',
                    multi_url: 'trade/tgmx/multi',
                    table: 'tgmx',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'tgid',
                sortName: 'tgid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'tgid', title: __('Tgid')},
                        {field: 'userid', title: __('Userid')},
                        {field: 'account', title: __('Account')},
                        {field: 'number', title: __('Number'), operate:'BETWEEN'},
                        {field: 'buy_number', title: __('Buy_number'), operate:'BETWEEN'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'fee', title: __('Fee'), operate:'BETWEEN'},
                        {field: 'total', title: __('Total'), operate:'BETWEEN'},
                        {field: 'buy_total', title: __('Buy_total'), operate:'BETWEEN'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'comtime', title: __('Comtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"3":__('Status 3'),"5":__('Status 5')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                queryParams: function (params) {

                    //这里可以追加搜索条件
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    console.log(filter.status);
                    if(typeof(filter.status) == "undefined"){
                        //这里可以动态赋值，比如从URL中获取admin_id的值，filter.admin_id=Fast.api.query('admin_id');
                        filter.status = 0;
                        op.admin_id = "=";
                        params.filter = JSON.stringify(filter);
                        params.op = JSON.stringify(op);
                    }
                    return params;
                },
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