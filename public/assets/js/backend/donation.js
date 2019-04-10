define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'donation/index',
                    add_url: 'donation/add',
                    edit_url: 'donation/edit',
                    del_url: 'donation/del',
                    multi_url: 'donation/multi',
                    table: 'donation',
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
                        {field: 'title', title: __('Title')},
                        {field: 'title_image', title: __('Title_image'), formatter: Table.api.formatter.image},
                        {field: 'subtitle', title: __('Subtitle')},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'amount_donated', title: __('Amount_donated'), operate:'BETWEEN'},
                        {field: 'number', title: __('Number')},
                        {field: 'switch', title: __('Switch'), visible:false, searchList: {"0":__('Switch 0'),"1":__('Switch 1')}},
                        {field: 'switch_text', title: __('Switch'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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