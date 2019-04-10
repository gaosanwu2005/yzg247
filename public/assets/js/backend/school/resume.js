define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'school/resume/index',
                    add_url: 'school/resume/add',
                    edit_url: 'school/resume/edit',
                    del_url: 'school/resume/del',
                    multi_url: 'school/resume/multi',
                    table: 'school_resume',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'uid',
                sortName: 'uid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'uid', title: __('Uid')},
                        {field: 'realname', title: __('Realname')},
                        {field: 'sex', title: __('Sex')},
                        {field: 'type', title: __('Type')},
                        {field: 'national', title: __('National')},
                        {field: 'idcard', title: __('Idcard')},
                        {field: 'birthday', title: __('Birthday'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'address', title: __('Address')},
                        {field: 'address2', title: __('Address2')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'mobile2', title: __('Mobile2')},
                        { field: 'idcardimg', title: __('Idcardimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        { field: 'graduationimg', title: __('Graduationimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        { field: 'headimg', title: __('Headimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
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