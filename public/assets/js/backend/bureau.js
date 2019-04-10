define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bureau/index',
                    add_url: 'bureau/add',
                    edit_url: 'bureau/edit',
                    del_url: 'bureau/del',
                    multi_url: 'bureau/multi',
                    table: 'bureau',
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
                        {field: 'uid', title: __('Uid')},
                        {field: 'realname', title: __('Realname')},
                        {field: 'sex', title: __('Sex')},
                        {field: 'idtype', title: __('Idtype'), visible:false, searchList: {"1":__('Idtype 1'),"2":__('Idtype 2')}},
                        {field: 'idtype_text', title: __('Idtype'), operate:false},
                        {field: 'national', title: __('National')},
                        {field: 'idcard', title: __('Idcard')},
                        {field: 'birthday', title: __('Birthday'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'address', title: __('Address')},
                        {field: 'residence', title: __('Residence')},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'mobile2', title: __('Mobile2')},
                        {field: 'idcardimg', title: __('Idcardimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'headimg', title: __('Headimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'school', title: __('School')},
                        {field: 'category', title: __('Category')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'create_dt', title: __('Create_dt')},
                        {field: 'exam_dt', title: __('Exam_dt'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'email', title: __('Email')},
                        {field: 'email2', title: __('Email2')},
                        {field: 'bureau_type', title: __('Bureau_type'), visible:false, searchList: {"1":__('Bureau_type 1'),"2":__('Bureau_type 2'),"3":__('Bureau_type 3')}},
                        {field: 'bureau_type_text', title: __('Bureau_type'), operate:false},
                        {field: 'category2', title: __('Category2')},
                        {field: 'grade', title: __('Grade')},
                        {field: 'graduationimg', title: __('Graduationimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'applicationimg', title: __('Applicationimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'commitmentimg', title: __('Commitmentimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'highlightimg', title: __('Highlightimg'), formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'politics', title: __('Politics')},
                        {field: 'education', title: __('Education')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    title: '审核',
                                    text: '审核',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'bureau/apply',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
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