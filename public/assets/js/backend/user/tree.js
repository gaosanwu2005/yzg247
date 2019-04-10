define(['jquery', 'bootstrap', 'backend', 'table', 'form','ztree'], function ($, undefined, Backend, Table, Form, undefined2) {

    var Controller = {
        index: function () {
                $(document).ready(function () {
                    $.ajax({
                        type: "POST",
                        url: "user/tree/mytree",
                        data: $('#search-form2').serialize(),// 你的formid
                        success: function (e) {
                            $("#treeDemo").html('');
                            var setting = {};
                            var zNodes = e;
                            $.fn.zTree.init($("#treeDemo"), setting, zNodes);
                            $.fn.zTree.getZTreeObj("treeDemo").expandAll(true);
                        }
                    });
                });
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