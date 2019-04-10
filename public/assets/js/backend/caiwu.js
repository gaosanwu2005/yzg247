define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'caiwu/index',
                    add_url: 'caiwu/add',
                    edit_url: 'caiwu/edit',
                    del_url: 'caiwu/del',
                    multi_url: 'caiwu/multi',
                    table: 'caiwu',
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
                        {field: 'userid', title: __('Userid'), formatter: Table.api.formatter.search},
                        {field: 'account', title: __('Account'), formatter: Table.api.formatter.search},
                        {field: 'yprice', title: __('Yprice'), operate:'BETWEEN'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'nprice', title: __('Nprice'), operate:'BETWEEN'},
                        {field: 'type', title: __('Type')},
                        {field: 'ptype', title: __('Ptype'), formatter: Table.api.formatter.search},
                        {field: 'memo', title: __('Memo')},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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