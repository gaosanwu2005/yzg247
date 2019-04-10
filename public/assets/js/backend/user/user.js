define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
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
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: __('账户管理'),
                                    text: '账户',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/account_edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('编辑用户'),
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '冻结',
                                    text: '冻结',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/delete_user',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '送矿机',
                                    text: '送矿机',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/song',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username'),formatter: Controller.api.formatter.user},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image},
                        {field: 'wall1', title: __('Wall1'), operate:'BETWEEN'},
                        {field: 'wall2', title: __('Wall2'), operate:'BETWEEN'},
                        {field: 'freeze1', title: '源币冻结', operate:'BETWEEN'},
                        {field: 'freeze2', title: 'lmc冻结', operate:'BETWEEN'},
                        {field: 'wall3', title: __('Wall3'), operate:'BETWEEN'},
                        {field: 'wall5', title: __('Wall5'), operate:'BETWEEN'},
                        {field: 'wall4', title: __('Wall4'), operate:'BETWEEN'},
                        // {field: 'wall6', title: __('Wall6'), operate:'BETWEEN'},
                        {field: 'wall7', title: __('Wall7'), operate:'BETWEEN'},
                        {field: 'v1', title: '令牌', operate:'BETWEEN'},
                        // {field: 'wall8', title: __('Wall8'), operate:'BETWEEN'},
                        {field: 'level', title: __('Level'), visible:false, operate:false},
                        {field: 'level_text', title: __('Level'), operate:false},
                        {field: 'loginip', title: __('Loginip')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'tjuser', title: __('Tjuser')},
                        {field: 'ztnum', title: __('Ztnum')},
                        {field: 'tdnum', title: __('Tdnum')},
                        {field: 'slrate', title: '个人算力'},
                        {field: 'tdsl', title: '团队算力'},
                        {field: 'star', title: '信用'},
                        {field: 'issm', title: '实名', searchList: {"0":'否',"1":'是'}, formatter: Controller.api.formatter.sm},
                        // {field: 'is_real', title: '实体', searchList: {"0":'否',"1":'是'}},
                        // {field: 'txwall6', title: '动态提现'},
                        // {field: 'txwall3', title: '互助提现'},
                        // {field: 'futou', title: '第N轮'},
                        // {field: 'slrate', title: __('Slrate')},
                        // {field: 'tdsl', title: __('Tdsl')},
                        // {field: 'tzprice', title: __('Tzprice')},
                        // {field: 'extsl', title: __('Extsl')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1')}},
                        //自定义栏位,custom是不存在的字段
                        {field: 'custom', title: '激活', operate: false, formatter: Controller.api.formatter.jh},
                        {field: 'status_text', title: __('Status'), operate:false, formatter: Controller.api.formatter.dong},

                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        index2: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    index_url2: 'user/user/index2',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url2,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: __('账户管理'),
                                    text: '账户',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/account_edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('编辑用户'),
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '冻结',
                                    text: '冻结',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/delete_user',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: '送矿机',
                                    text: '送矿机',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/song',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username'),formatter: Controller.api.formatter.user},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image},
                        {field: 'wall1', title: __('Wall1'), operate:'BETWEEN'},
                        {field: 'wall2', title: __('Wall2'), operate:'BETWEEN'},
                        {field: 'wall3', title: __('Wall3'), operate:'BETWEEN'},
                        {field: 'wall5', title: __('Wall5'), operate:'BETWEEN'},
                        {field: 'wall4', title: __('Wall4'), operate:'BETWEEN'},
                        // {field: 'wall6', title: __('Wall6'), operate:'BETWEEN'},
                        // {field: 'wall7', title: __('Wall7'), operate:'BETWEEN'},
                        // {field: 'wall8', title: __('Wall8'), operate:'BETWEEN'},
                        {field: 'level', title: __('Level'), visible:false, operate:false},
                        {field: 'level_text', title: __('Level'), operate:false},
                        {field: 'loginip', title: __('Loginip')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'tjuser', title: __('Tjuser')},
                        {field: 'ztnum', title: __('Ztnum')},
                        {field: 'tdnum', title: __('Tdnum')},
                        {field: 'star', title: '信用'},
                        {field: 'issm', title: '实名', searchList: {"0":'否',"1":'是'}, formatter: Controller.api.formatter.sm},
                        // {field: 'is_real', title: '实体', searchList: {"0":'否',"1":'是'}},
                        // {field: 'txwall6', title: '动态提现'},
                        // {field: 'txwall3', title: '互助提现'},
                        // {field: 'futou', title: '第N轮'},
                        // {field: 'slrate', title: __('Slrate')},
                        // {field: 'tdsl', title: __('Tdsl')},
                        // {field: 'tzprice', title: __('Tzprice')},
                        // {field: 'extsl', title: __('Extsl')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1')}},
                        //自定义栏位,custom是不存在的字段
                        {field: 'custom', title: '激活', operate: false, formatter: Controller.api.formatter.jh},
                        {field: 'status_text', title: __('Status'), operate:false, formatter: Controller.api.formatter.dong},

                    ]
                ],

                queryParams: function (params) {
                    //这里可以追加搜索条件
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    //这里可以动态赋值，比如从URL中获取admin_id的值，filter.admin_id=Fast.api.query('admin_id');
                    filter.jointime = 0;
                    op.admin_id = "=";
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        index3: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    index_url3: 'user/user/index3',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url3,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username'),formatter: Controller.api.formatter.user},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'wall1', title: __('Wall1'), operate:'BETWEEN'},
                        {field: 'wall2', title: __('Wall2'), operate:'BETWEEN'},
                        {field: 'wall3', title: __('Wall3'), operate:'BETWEEN'},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image},
                        {field: 'level', title: __('Level'), visible:false, operate:false},
                        {field: 'level_text', title: __('Level'), operate:false},
                        {field: 'loginip', title: __('Loginip')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'tjuser', title: __('Tjuser')},
                        {field: 'ztnum', title: __('Ztnum')},
                        {field: 'tdnum', title: __('Tdnum')},
                        {field: 'star', title: '信用'},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1')}},
                        //自定义栏位,custom是不存在的字段
                        {field: 'agent_name', title: '代理申请'},
                        {field: 'is_agent', title: '代理审核', operate: false, formatter: Controller.api.formatter.dai},
                        {field: 'custom', title: '激活', operate: false, formatter: Controller.api.formatter.jh},
                        {field: 'status_text', title: __('Status'), operate:false, formatter: Controller.api.formatter.dong},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: __('账户管理'),
                                    text: '账户',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/account_edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('编辑用户'),
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons}
                    ]
                ],

                queryParams: function (params) {
                    //这里可以追加搜索条件
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    //这里可以动态赋值，比如从URL中获取admin_id的值，filter.admin_id=Fast.api.query('admin_id');
                    filter.agent = 0;
                    op.agent = ">";
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        index4: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    index_url4: 'user/user/index4',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url4,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username'),formatter: Controller.api.formatter.user},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'wall1', title: __('Wall1'), operate:'BETWEEN'},
                        {field: 'wall2', title: __('Wall2'), operate:'BETWEEN'},
                        {field: 'wall3', title: __('Wall3'), operate:'BETWEEN'},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image},
                        {field: 'level', title: __('Level'), visible:false, operate:false},
                        {field: 'level_text', title: __('Level'), operate:false},
                        {field: 'tjuser', title: __('Tjuser')},
                        {field: 'ztnum', title: __('Ztnum')},
                        {field: 'tdnum', title: __('Tdnum')},
                        {field: 'star', title: '信用'},
                        {field: 'shopname', title: '店铺名'},
                        {field: 'shopcate', title: '店铺类别'},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1')}},
                        {field: 'shop_open', title: '开店审核', operate: false, formatter: Controller.api.formatter.shop},
                        {field: 'status_text', title: __('Status'), operate:false, formatter: Controller.api.formatter.dong},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: __('账户管理'),
                                    text: '账户',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/account_edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('编辑用户'),
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons}
                    ]
                ],

                queryParams: function (params) {
                    //这里可以追加搜索条件
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    //这里可以动态赋值，比如从URL中获取admin_id的值，filter.admin_id=Fast.api.query('admin_id');
                    filter.shop_open = 0;
                    op.shop_open = ">";
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    return params;
                },
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        index5: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    index_url5: 'user/user/index5',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url5,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username'),formatter: Controller.api.formatter.user},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'wall1', title: __('Wall1'), operate:'BETWEEN'},
                        {field: 'wall2', title: __('Wall2'), operate:'BETWEEN'},
                        {field: 'wall3', title: __('Wall3'), operate:'BETWEEN'},
                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image},
                        {field: 'idcard_1', title: '身份证正面', formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'idcard_2', title: '身份证反面', formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'business', title: '营业执照', formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'business2', title: '门面照片', formatter: Table.api.formatter.image, events: Table.api.events.img},
                        {field: 'level', title: __('Level'), visible:false, operate:false},
                        {field: 'level_text', title: __('Level'), operate:false},
                        {field: 'tjuser', title: __('Tjuser')},
                        {field: 'ztnum', title: __('Ztnum')},
                        {field: 'tdnum', title: __('Tdnum')},
                        {field: 'star', title: '信用'},
                        {field: 'shopname', title: '店铺名'},
                        {field: 'shopcate', title: '店铺类别'},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"0":__('Status 0'),"1":__('Status 1')}},
                        {field: 'is_real', title: '实体审核', operate: false, formatter: Controller.api.formatter.real},
                        {field: 'status_text', title: __('Status'), operate:false, formatter: Controller.api.formatter.dong},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [

                                {
                                    name: 'detail',
                                    title: __('账户管理'),
                                    text: '账户',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/account_edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                                {
                                    name: 'detail',
                                    title: __('编辑用户'),
                                    text: '编辑',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/user/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons}
                    ]
                ],

                queryParams: function (params) {
                    //这里可以追加搜索条件
                    var filter = JSON.parse(params.filter);
                    var op = JSON.parse(params.op);
                    //这里可以动态赋值，比如从URL中获取admin_id的值，filter.admin_id=Fast.api.query('admin_id');
                    filter.is_real = 0;
                    op.is_real = ">";
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
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
            },
            formatter: {//渲染的方法

                user: function (value, row, index) {
                    return '<a href="/api/user/syslogin/uid/'+row.id+'" target="_blank" class="btn btn-xs btn-ip bg-success">  ' + value + '</a>';
                },
                jh: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="user/user/jhuser" data-id="' + row.id+'" data-action="is_new" data-params="'+(value == '否' ? '1' : '0')+'">' + (row.jointime > 0 ? '已激活' : '未激活') + '</a>';
                },
                sm: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/userchange" data-id="' + row.id+'" data-action="issm" data-params="'+(value == 1 ? '0' : '1')+'">' + (value ==1 ? '是' : '否') + '</a>';
                },
                dai: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/change3" data-id="' + row.id+'" data-action="is_agent" data-params="1">' + (row.is_agent == 1 ? '已审核' : '未审核') + '</a>';
                },
                shop: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/change4" data-id="' + row.id+'" data-action="shop_open" data-params="1">' + (row.shop_open == 1 ? '已审核' : '未审核') + '</a>';
                },
                real: function (value, row, index) {
                    //添加上btn-change可以自定义请求的URL进行数据处理
                    return '<a class="btn btn-xs btn-ip btn-change bg-success" data-url="ajax/change5" data-id="' + row.id+'" data-action="is_real" data-params="1">' + (row.is_real == 1 ? '已审核' : '未审核') + '</a>';
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