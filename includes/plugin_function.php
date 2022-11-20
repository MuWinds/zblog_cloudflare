<?php

function ActivePlugin_zblogofcloudflare()
{
    global $zbp, $enable_scene;
    $enable_scene = json_decode($zbp->Config('zblogofcloudflare')->EnableScene, true);

    Add_Filter_Plugin('Filter_Plugin_PostArticle_Succeed', 'cloudflare_cdn_Post');
    Add_Filter_Plugin('Filter_Plugin_DelArticle_Succeed', 'cloudflare_cdn_Post');
    Add_Filter_Plugin('Filter_Plugin_PostPage_Succeed', 'cloudflare_cdn_Post');
    Add_Filter_Plugin('Filter_Plugin_DelPage_Succeed', 'cloudflare_cdn_Post');
    Add_Filter_Plugin('Filter_Plugin_PostCategory_Succeed', 'cloudflare_cdn_Category');
    Add_Filter_Plugin('Filter_Plugin_DelCategory_Succeed', 'cloudflare_cdn_Category');
    Add_Filter_Plugin('Filter_Plugin_PostComment_Succeed', 'cloudflare_cdn_Comm');
    Add_Filter_Plugin('Filter_Plugin_DelComment_Succeed', 'cloudflare_cdn_Comm');
}

/**
 * @return array 返回CloudFlare配置
 */
function cloudflare_cdn_getKey()
{
    global $zbp;
        return [
            'ZoneID'  => $zbp->Config('zblogofcloudflare')->ZoneID,
            'APIToken' => $zbp->Config('zblogofcloudflare')->APIToken,
        ];
    }

//缓存刷新函数
function cloudflare_cdn_RefreshCache($url)
{
    global $zbp;
    $cloudflare_cdn_config = cloudflare_cdn_getKey();
    $zoneid = $cloudflare_cdn_config['ZoneID'];
    $apitoken = $cloudflare_cdn_config['APIToken'];
    // 发起清除缓存的请求
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/$zoneid/purge_cache");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "[$url]");

    $headers = array();
    $headers[] = "Authorization: $apitoken";
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}


function InstallPlugin_zblogofcloudflare()
{
    global $zbp, $cloudflare_cdn;

    if (!$zbp->Config('zblogofcloudflare')->HasKey('version')) {
        $zbp->Config('zblogofcloudflare')->version = 1.0;
        $zbp->Config('zblogofcloudflare')->AccountID = '';
        $zbp->Config('zblogofcloudflare')->APIKey = '';
        $zbp->Config('zblogofcloudflare')->EnableScene = json_encode($cloudflare_cdn['scene']);
        $zbp->SaveConfig('zblogofcloudflare');
    }
    $zbp->Config('zblogofcloudflare')->version = 1.0;
    $zbp->SaveConfig('zblogofcloudflare');
}

function UninstallPlugin_zblogofcloudlfare()
{
}


function cloudflare_cdn_Post(&$article)
{
    global $zbp, $enable_scene;
    if ($article->Status == '0') {
        if ($enable_scene['post']['current_post']) {
            cloudflare_cdn_RefreshCache($article->Url);//文章
        }
        if ($enable_scene['post']['current_category_all_post']) {
            cloudflare_cdn_RefreshCache($article->Category->Url);//分类
        }
        if ($enable_scene['post']['index']) {
            cloudflare_cdn_RefreshCache($zbp->host);//首页
        }
    }
}

function cloudflare_cdn_Comm(&$cmt)
{
    global $zbp, $enable_scene;
    $postid = $cmt->LogID;
    $article = $zbp->GetPostByID($postid);
    if ($enable_scene['comment']['current_post']) {
        cloudflare_cdn_RefreshCache($article->Url);//文章
    }
    if ($enable_scene['comment']['current_category_all_post']) {
        cloudflare_cdn_RefreshCache($article->Category->Url);//分类
    }
    if ($enable_scene['comment']['index']) {
        cloudflare_cdn_RefreshCache($zbp->host);//首页
    }
}

function cloudflare_cdn_Category(&$cate)
{
    global $zbp, $enable_scene;
    if ($enable_scene['category']['current_category_all_post']) {
        tencentcloud_cdn_RefreshCache($cate->Url);//分类
    }
    if ($enable_scene['category']['index']) {
        tencentcloud_cdn_RefreshCache($zbp->host);//首页
    }
}