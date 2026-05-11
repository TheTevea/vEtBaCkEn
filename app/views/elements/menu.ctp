<script type="text/javascript">
    $(document).ready(function () {
        $("#nav").find("li:has(span):not(:has(li))").hide();
        $(".dir").parent(":not(:has(ul))").find(".dir").removeAttr("class");
        $("#nav").find("li:has(span):has(li)").each(function(){
            if($(this).html().replace(/<li><\/li>/g,"").indexOf("<li>")==-1){
                $(this).hide();
            }
        });
    });
</script>
<?php

if ($this->params['controller'] != 'users' || ($this->params['controller'] == 'users' && $this->params['action'] != 'login')) {
    $this->element('check_access');
    $str = '';
    if (!empty($menu)) {
        $tmp = '';
        foreach ($menu as $index => $menuItem) {
            $access = true;
            if(SERVER_TYPE == '1'){
                if($menuItem['url'] == '/sync_monitors/server' || $menuItem['url'] == '/sync_monitors/index'){
                    if($user['User']['type'] != 1){
                        $sqlServer = mysql_query("SELECT COUNT(id) FROM offline_servers WHERE offline_project_id = ".$user['User']['offline_project_id']." AND status > 0");
                        $rowServer = mysql_fetch_array($sqlServer);
                        if($rowServer[0] > 1){
                            $access = true;
                        } else {
                            $access = false;
                        }
                    }
                }
            }
            if($access == true){
                $classDir = 'dir';
                if (empty($menuItem['submenu'])) {
                    $classDir = '';
                }
                $tmp.='<li>';
                if ($menuItem['url'] != '') {
                    $url = explode("/", $menuItem['url']);
                    for ($i = 0; $i < sizeof($url); $i++) {
                        if ($url[$i] != '') {
                            $urlController = $url[$i];
                            $urlView = $url[$i + 1];
                            break;
                        }
                    }
                    if (checkAccess($user['User']['id'], $urlController, $urlView)) {
                        $tmp.=$html->link(__($menuItem['text'], true), '/' . $menuItem['url'], array('class' => $classDir . ' ' . $menuItem['target'], 'escape' => false));
                    }
                } else {
                    $tmp.=$this->Html->tag('span', $menuItem['text'], array('class' => $classDir));
                }
                if (!empty($menuItem['submenu'])) {
                    $subTmp = '';
                    foreach ($menuItem['submenu'] as $subMenu) {
                        $classDir = 'dir';
                        if (empty($subMenu['submenu'])) {
                            $classDir = '';
                        }
                        $subTmp.='<li>';
                        if ($subMenu['url'] != '') {
                            $url = explode("/", $subMenu['url']);
                            for ($i = 0; $i < sizeof($url); $i++) {
                                if ($url[$i] != '') {
                                    $urlController = $url[$i];
                                    $urlView = $url[$i + 1];
                                    break;
                                }
                            }
                            if (checkAccess($user['User']['id'], $urlController, $urlView)) {
                                $subTmp.=$html->link(__($subMenu['text'], true), '/' . $subMenu['url'], array('class' => $classDir . ' ' . $subMenu['target'], 'escape' => false));
                            }
                        } else {
                            $subTmp.=$this->Html->tag('span', $subMenu['text'], array('class' => $classDir));
                        }
                        if (!empty($subMenu['submenu'])) {
                            $subSubTmp = '';
                            foreach ($subMenu['submenu'] as $subSubMenu) {
                                $url = explode("/", $subSubMenu['url']);
                                for ($i = 0; $i < sizeof($url); $i++) {
                                    if ($url[$i] != '') {
                                        $urlController = $url[$i];
                                        $urlView = $url[$i + 1];
                                        break;
                                    }
                                }
                                if (checkAccess($user['User']['id'], $urlController, $urlView)) {
                                    $subSubTmp.='<li>' . $html->link(__($subSubMenu['text'], true), '/' . $subSubMenu['url'], array('class' => $subSubMenu['target'], 'escape' => false)) . '</li>';
                                }
                            }
                            if ($subSubTmp != '') {
                                $subTmp.='<ul>' . $subSubTmp . '</ul>';
                            }
                        }
                        $subTmp.='</li>';
                    }
                    if (str_replace(array("<li>", "</li>"), "", $subTmp) != '') {
                        $tmp.='<ul>' . $subTmp . '</ul>';
                    }
                }
                $tmp.='</li>';
            }
        }
    }
    if (str_replace(array("<li>", "</li>"), "", $tmp) != '') {
        $str = '<ul id="nav" class="dropdown dropdown-horizontal">' . $tmp . '</ul>';
    }
}
echo $str;
?>