define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'letou/index',
                    add_url: 'letou/add',
                    edit_url: 'letou/edit',
                    del_url: 'letou/del',
                    multi_url: 'letou/multi',
                    table: 'letou',
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
                        {field: 'qi', title: __('Qi')},
                        {field: 'N1', title: __('N1'), formatter: Controller.api.getwx},
                        {field: 'N2', title: __('N2'), formatter: Controller.api.getwx},
                        {field: 'N3', title: __('N3'), formatter: Controller.api.getwx},
                        {field: 'N4', title: __('N4'), formatter: Controller.api.getwx},
                        {field: 'N5', title: __('N5'), formatter: Controller.api.getwx},
                        {field: 'M1', title: __('M1'), formatter: Controller.api.getwx},
                        {field: 'M2', title: __('M2'), formatter: Controller.api.getwx},
                        {field: 'date', title: __('Date'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'ysl', title: __('Ysl'), formatter: Controller.api.ysl},
                        {field: 'gz', title: '干支',formatter: Controller.api.gz},
                        {field: 'week', title: __('Week')},
                        {field: 'beizhu', title: __('Beizhu'), formatter: Controller.api.bz},
                        {field: 'plan', title: __('Plan'), formatter: Controller.api.pl},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"successed":__('successed'),"failured":__('failured')}},
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
            },
            getwx: function (value, row, index) {
                nayin = [
                    ["甲子" , "海中金"],
                    ["乙丑" , "海中金"],
                    ["丙寅" , "炉中火"],
                    ["丁卯" , "炉中火"],
                    ["戊辰" , "大林木"],
                    ["己巳" , "大林木"],
                    ["庚午" , "路旁土"],
                    ["辛未" , "路旁土"],
                    ["壬申" , "剑锋金"],
                    ["癸酉" , "剑锋金"],
                    ["甲戌" , "山头火"],
                    ["乙亥" , "山头火"],
                    ["丙子" , "洞下水"],
                    ["丁丑" , "洞下水"],
                    ["戊寅" , "城墙土"],
                    ["己卯" , "城墙土"],
                    ["庚辰" , "白腊金"],
                    ["辛巳" , "白腊金"],
                    ["壬午" , "杨柳木"],
                    ["癸未" , "杨柳木"],
                    ["甲申" , "泉中水"],
                    ["乙酉" , "泉中水"],
                    ["丙戌" , "屋上土"],
                    ["丁亥" , "屋上土"],
                    ["戊子" , "霹雷火"],
                    ["己丑" , "霹雷火"],
                    ["庚寅" , "松柏木"],
                    ["辛卯" , "松柏木"],
                    ["壬辰" , "常流水"],
                    ["癸巳" , "常流水"],
                    ["甲午" , "沙中金"],
                    ["乙未" , "沙中金"],
                    ["丙申" , "山下火"],
                    ["丁酉" , "山下火"],
                    ["戊戌" , "平地木"],
                    ["己亥" , "平地木"],
                    ["庚子" , "壁上土"],
                    ["辛丑" , "壁上土"],
                    ["壬寅" , "金箔金"],
                    ["癸卯" , "金箔金"],
                    ["甲辰" , "佛灯火"],
                    ["乙巳" , "佛灯火"],
                    ["丙午" , "天河水"],
                    ["丁未" , "天河水"],
                    ["戊申" , "大驿土"],
                    ["己酉" , "大驿土"],
                    ["庚戌" , "钗钏金"],
                    ["辛亥" , "钗钏金"],
                    ["壬子" , "桑松木"],
                    ["癸丑" , "桑松木"],
                    ["甲寅" , "大溪水"],
                    ["乙卯" , "大溪水"],
                    ["丙辰" , "沙中土"],
                    ["丁巳" , "沙中土"],
                    ["戊午" , "天上火"],
                    ["己未" , "天上火"],
                    ["庚申" , "石榴木"],
                    ["辛酉" , "石榴木"],
                    ["壬戌" , "大海水"],
                    ["癸亥" , "大海水"]];
                return   value+'<br>'+nayin[value-1][0]+'<br>'+nayin[value-1][1];
            },
            ysl: function (value, row, index) {
                gan = ['天干', '甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
                return  gan[value];
            },
            gz: function (value, row, index) {
                gan = ['天干', '甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
                zhi = ['地支', '子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];
                return  gan[row.ngank]+gan[row.ygank]+gan[row.rgank]+gan[row.sgank]+'<br>'
                    +zhi[row.nzhik]+zhi[row.yzhik]+zhi[row.rzhik]+zhi[row.szhik];
            },
            bz: function (value, row, index) {
                return  '<div style="width: 150px">'+value+'</div>'
            },
            pl: function (value, row, index) {
                if(value!=null){
                    return  '<div style="width: 150px;word-break: break-all;">'+value.toString()+'</div>';
                }

            }
        }
    };
    return Controller;
});