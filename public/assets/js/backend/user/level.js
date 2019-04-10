define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/level/index',
                    add_url: 'user/level/add',
                    edit_url: 'user/level/edit',
                    del_url: 'user/level/del',
                    multi_url: 'user/level/multi',
                    table: 'user_level',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'level_id',
                sortName: 'level_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'level_id', title: __('Level_id')},
                        {field: 'level_name', title: __('Level_name')},
                        {field: 'suan', title: __('Suan'), operate:'BETWEEN'},
                        {field: 'ztnum', title: __('Ztnum')},
                        {field: 'tdnum', title: __('Tdnum')},
                        {field: 'tdj', title: __('Tdj'), operate:'BETWEEN'},
                        {field: 'ceng', title: __('Ceng')},
                        {field: 'deposit', title: __('Deposit')},
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