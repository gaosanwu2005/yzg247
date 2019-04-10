define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trade2/tgmx2/index',
                    add_url: 'trade2/tgmx2/add',
                    edit_url: 'trade2/tgmx2/edit',
                    del_url: 'trade2/tgmx2/del',
                    multi_url: 'trade2/tgmx2/multi',
                    table: 'tgmx2',
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
                        // {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'fee', title: __('Fee'), operate:'BETWEEN'},
                        // {field: 'total', title: __('Total'), operate:'BETWEEN'},
                        // {field: 'buy_total', title: __('Buy_total'), operate:'BETWEEN'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'comtime', title: __('Comtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"3":__('Status 3'),"4":'冻结',"5":__('Status 5')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: '手动匹配',
                                    text: '手动匹配',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'trade2/tgmx2/sdpp',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },

                                {
                                    name: 'detail',
                                    title: __('编辑用户'),
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'trade2/tgmx2/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons}
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