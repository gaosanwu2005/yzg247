define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trade/ppmx/index',
                    add_url: 'trade/ppmx/add',
                    edit_url: 'trade/ppmx/edit',
                    del_url: 'trade/ppmx/del',
                    multi_url: 'trade/ppmx/multi',
                    table: 'ppmx',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ppid',
                sortName: 'ppid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'ppid', title: __('Ppid')},
                        {field: 'tgid', title: __('Tgid')},
                        {field: 'userid', title: __('Userid')},
                        {field: 'account', title: __('Account')},
                        {field: 'xyid', title: __('Xyid')},
                        {field: 'userid1', title: __('Userid1')},
                        {field: 'account1', title: __('Account1')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'number', title: __('Number')},
                        {field: 'total', title: __('Total'), operate:'BETWEEN'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'paytime', title: __('Paytime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'confirmtime', title: __('Confirmtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'pimg', title: __('Pimg'), formatter: Table.api.formatter.image, operate: false, events: Table.api.events.img},
                        {field: 'payinfo', title: __('Payinfo')},
                        {field: 'tsimg', title: __('Tsimg'), formatter: Table.api.formatter.image,operate: false, events: Table.api.events.img},
                        {field: 'tsinfo', title: __('Tsinfo')},
                        {field: 'tstime', title: __('Tstime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'reply', title:'平台处理'},
                        {field: 'type', title: __('Type'), visible:false, searchList: {"4":__('Type 4'),"5":__('Type 5')}},
                        {field: 'type_text', title: __('Type'), operate:false},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    title: '管理',
                                    text: '管理',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'trade/ppmx/pphand',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },

                            ],
                            formatter: Table.api.formatter.buttons}
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