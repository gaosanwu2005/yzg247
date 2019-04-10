define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'tx/lmc2eth/index',
                    add_url: 'tx/lmc2eth/add',
                    edit_url: 'tx/lmc2eth/edit',
                    del_url: 'tx/lmc2eth/del',
                    multi_url: 'tx/lmc2eth/multi',
                    table: 'tx_lmc2eth',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'account', title: __('Account')},
                        // {field: 'eth', title: __('Eth'), operate:'BETWEEN'},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'comtime', title: __('Comtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'userwallet', title: __('Userwallet')},
                        {field: 'paytype', title: __('Paytype')},
                        {field: 'service', title: __('Service'), operate:'BETWEEN'},
                        //自定义栏位,custom是不存在的字段
                        {field: 'custom', title: '审核', operate: false, formatter: Controller.api.formatter.jh},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            },
            formatter: {//渲染的方法

                user: function (value, row, index) {
                    return '<a href="/api/user/syslogin/uid/'+row.id+'" target="_blank" class="btn btn-xs btn-ip bg-success">  ' + value + '</a>';
                },
                jh: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    if(row.status==0){
                        return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/lmc2eth" data-id="' + row.id+'" data-action="is_new" data-params="1">同意</a>'+'<a class="btn btn-xs btn-ip btn-change bg-danner" data-url="ajax/lmc2eth" data-id="' + row.id+'" data-action="is_new" data-params="2">拒绝</a>';

                    }
                },
                dai: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/change3" data-id="' + row.id+'" data-action="is_agent" data-params="1">' + (row.is_agent == 1 ? '已审核' : '未审核') + '</a>';
                },
                dong: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn-change text-success" data-url="ajax/change2" data-id="' + row.id+'" data-action="status" data-params="'+(value == '冻结' ? '1' : '0')+'"><i class="fa ' + (value == '冻结' ? 'fa-toggle-off' : 'fa-toggle-on') + ' fa-2x"></i></a>';
                },

            },
        }
    };
    return Controller;
});