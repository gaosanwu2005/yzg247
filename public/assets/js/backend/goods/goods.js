define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/goods/index',
                    add_url: 'goods/goods/add',
                    edit_url: 'goods/goods/edit',
                    del_url: 'goods/goods/del',
                    multi_url: 'goods/goods/multi',
                    table: 'goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_id',
                sortName: 'goods_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'goods_sn', title: __('Goods_sn')},
                        {field: 'goods_name', title: __('Goods_name')},
                        {field: 'store_count', title: __('Store_count')},
                        {field: 'xian_price', title: __('Xian_price'), operate:'BETWEEN'},
                        {field: 'sybl', title: __('Sybl')},
                        {field: 'kjsl', title: __('Kjsl')},
                        {field: 'yxzq', title: __('Yxzq')},
                        {field: 'goods_remark', title: __('Goods_remark')},
                        {field: 'originalimage', title: __('Originalimage'), formatter: Table.api.formatter.image},
                        {field: 'on_time', title: __('On_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'sort', title: __('Sort')},
                        {field: 'xg', title: __('Xg')},
                        {field: 'is_on', title: __('Is_on'), visible:false, searchList: {"1":__('Is_on 1'),"0":__('Is_on 0')}},
                        {field: 'is_on_text', title: __('Is_on'), operate:false},
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