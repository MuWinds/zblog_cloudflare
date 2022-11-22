<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
global $cloudflare_cdn_global, $cloudflare_cdn;
if (!$zbp->CheckRights($action)) {
    $zbp->ShowError(6);
    die();
}
if (!$zbp->CheckPlugin('zblogofcloudflare')) {
    $zbp->ShowError(48);
    die();
}

$enable_scene = json_decode($zbp->Config('zblogofcloudflare')->EnableScene, true);

if (count($_POST) > 0) {
    CheckIsRefererValid();
    $zbp->Config('zblogofcloudflare')->ZoneID = GetVars('ZoneID', 'POST');
    $zbp->Config('zblogofcloudflare')->APIToken = GetVars('APIToken', 'POST');

    foreach ($enable_scene as $k => $v) {
        foreach ($v as $kk => $vv) {
            $enable_scene[$k][$kk] = 0;
        }
    }

    foreach (['post', 'comment', 'category'] as $key) {
        if (GetVars($key, 'POST')) {
            foreach (GetVars($key, 'POST') as $v) {
                $enable_scene[$key][$v] = 1;
            }
        }
    }

    $zbp->Config('zblogofcloudflare')->EnableScene = json_encode($enable_scene);
    $zbp->SaveConfig('zblogofcloudflare');
    $zbp->SetHint('good');
    Redirect('main.php');
}

foreach ($enable_scene as $k => $v) {
    foreach ($v as $kk => $vv) {
        $enable_scene[$k][$kk] = [
            $cloudflare_cdn['translation'][$kk],
            $vv
        ];
    }
}

$blogtitle = 'CloudFlareCDN';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
    <div class="divHeader"><?php echo $blogtitle; ?></div>
    <div id="divMain2">
        <form method="post" action="">
            <?php echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">'; ?>
            <table border="1" width="100%" cellspacing="0" cellpadding="0" class="tableBorder tableBorder-thcenter">
                <tr>
                    <td><p><b>· ZoneID</b><br/>
                        </p></td>
                    <td>
                        <p>&nbsp;
                            <?php zbpform::text('ZoneID', $zbp->Config('zblogofcloudflare')->ZoneID, '450px'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                <tr>
                    <td><p><b>· API Token</b></p></td>
                    <td>
                        <p>&nbsp;
                            <?php zbpform::text('APIToken', $zbp->Config('zblogofcloudflare')->APIToken, '450px'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <p><a href="https://dash.cloudflare.com/" target="_blank" color="blue">查看ZoneID和APIToken</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p> 触发场景
                        </p>
                    </td>
                    <td>
                    </td>
                </tr>
                <tr>
                    <td><p><b>· 文章(页面)->发布/编辑/删除</b></p></td>
                    <td>
                        <p>&nbsp;
                            <?php zbpform::checkbox('post', $enable_scene['post'], '450px'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td><p><b>· 评论->发布/审核/删除</b></p></td>
                    <td>
                        <p>&nbsp;
                            <?php zbpform::checkbox('comment', $enable_scene['comment'], '450px'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td><p><b>· 分类->发布/编辑/删除</b></p></td>
                    <td>
                        <p>&nbsp;
                            <?php zbpform::checkbox('category', $enable_scene['category'], '450px'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <p>
                <input type="submit" class="button" value="提交" id="btnPost"/>
            </p>
        </form>
    </div>
</div>
<script type="text/javascript">ActiveLeftMenu('zblogofcloudflare')</script>
<script type="text/javascript">AddHeaderIcon("<?php echo $bloghost . 'zb_users/plugin/zblogofcloudflare/logo.png'; ?>")</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
