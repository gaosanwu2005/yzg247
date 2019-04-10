define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trade2/tzlist/index',
                    add_url: 'trade2/tzlist/add',
                    edit_url: 'trade2/tzlist/edit',
                    del_url: 'trade2/tzlist/del',
                    multi_url: 'trade2/tzlist/multi',
                    table: 'tzrank',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'rid',
                sortName: 'rid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'rid', title: __('Rid')},
                        {field: 'rprice', title: __('Rprice'), operate:'BETWEEN'},
                        {field: 'pdfee', title: __('Pdfee')},
                        {field: 'flrate', title: __('Flrate'), operate:'BETWEEN'},
                        {field: 'isdk', title: __('Isdk')},
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