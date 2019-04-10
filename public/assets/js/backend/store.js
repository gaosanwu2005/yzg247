define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'store/index',
                    add_url: 'store/add',
                    edit_url: 'store/edit',
                    del_url: 'store/del',
                    multi_url: 'store/multi',
                    table: 'store',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'store_id',
                sortName: 'store_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'store_id', title: __('Store_id')},
                        {field: 'store_name', title: __('Store_name')},
                        {field: 'grade_id', title: __('Grade_id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'user_name', title: __('User_name')},
                        {field: 'sc_id', title: __('Sc_id')},
                        {field: 'company_name', title: __('Company_name')},
                        {field: 'store_address', title: __('Store_address')},
                        {field: 'store_zip', title: __('Store_zip')},
                        {field: 'store_state', title: __('Store_state'), visible:false, searchList: {"0":__('Store_state 0'),"1":__('Store_state 1'),"2":__('Store_state 2')}},
                        {field: 'store_state_text', title: __('Store_state'), operate:false},
                        {field: 'store_close_info', title: __('Store_close_info')},
                        {field: 'store_sort', title: __('Store_sort')},
                        {field: 'store_rebate_paytime', title: __('Store_rebate_paytime')},
                        {field: 'store_time', title: __('Store_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'store_end_time', title: __('Store_end_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'store_logo', title: __('Store_logo')},
                        {field: 'store_banner', title: __('Store_banner')},
                        {field: 'store_avatar', title: __('Store_avatar')},
                        {field: 'seo_keywords', title: __('Seo_keywords')},
                        {field: 'seo_description', title: __('Seo_description')},
                        {field: 'store_aliwangwang', title: __('Store_aliwangwang')},
                        {field: 'store_qq', title: __('Store_qq')},
                        {field: 'store_phone', title: __('Store_phone')},
                        {field: 'store_recommend', title: __('Store_recommend'), visible:false, searchList: {"0":__('Store_recommend 0'),"1":__('Store_recommend 1')}},
                        {field: 'store_recommend_text', title: __('Store_recommend'), operate:false},
                        {field: 'store_credit', title: __('Store_credit')},
                        {field: 'store_desccredit', title: __('Store_desccredit'), operate:'BETWEEN'},
                        {field: 'store_servicecredit', title: __('Store_servicecredit'), operate:'BETWEEN'},
                        {field: 'store_deliverycredit', title: __('Store_deliverycredit'), operate:'BETWEEN'},
                        {field: 'store_collect', title: __('Store_collect')},
                        {field: 'store_workingtime', title: __('Store_workingtime')},
                        {field: 'store_warning_storage', title: __('Store_warning_storage')},
                        {field: 'store_free_time', title: __('Store_free_time')},
                        {field: 'ensure', title: __('Ensure'), visible:false, searchList: {"1":__('Ensure 1')}},
                        {field: 'ensure_text', title: __('Ensure'), operate:false},
                        {field: 'deposit', title: __('Deposit'), operate:'BETWEEN'},
                        {field: 'deposit_icon', title: __('Deposit_icon'), searchList: {"1":__('Deposit_icon 1')}, formatter: Table.api.formatter.icon},
                        {field: 'store_money', title: __('Store_money'), operate:'BETWEEN'},
                        {field: 'pending_money', title: __('Pending_money'), operate:'BETWEEN'},
                        {field: 'service_phone', title: __('Service_phone')},
                        {field: 'latitude', title: __('Latitude')},
                        {field: 'longitude', title: __('Longitude')},
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