define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'school/info/index',
                    add_url: 'school/info/add',
                    edit_url: 'school/info/edit',
                    del_url: 'school/info/del',
                    multi_url: 'school/info/multi',
                    table: 'school_info',
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
                        { field: 'title', title: __('Title'),operate: 'LIKE %...%',},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'sort', title: __('Sort')},
                        {field: 'is_on', title: __('Is_on'), visible:false, searchList: {"1":__('Is_on 1'),"0":__('Is_on 0')}},
                        {field: 'is_on_text', title: __('Is_on'), operate:false},
                        {field: 'category_id', title: __('Category_id')},
                        {field: 'classtype_id', title: __('Classtype_id')},
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