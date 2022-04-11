<?php

/*
Plugin Name: Xarr-Notify-Wordpress-Plugin
Plugin URI: https://xarr-notify.52nyg.com/
Description:  Xarr-Notify Wordpress插件
Version: 1.0.0
Author: 包子
Author URI: https://blog.52nyg.com/
*/

function commentXarr_initFunction()
{
    // 为 讨论 页面注册新设置
    register_setting('discussion', 'xarrnotify_api');

    // 在讨论读页面上注册新分节
    add_settings_section(
        'commentXarr_settings_section',
        'XArr-Notify 推送配置',
        'commentXarr_settings_section_cb',
        'discussion'
    );

    // 讨论页面中，在 commentXarr_settings_section 分节上注册新设置
    add_settings_field(
        'commentXarr_settings_field',
        'XArr-Notify 推送链接',
        'commentXarr_settings_field_cb',
        'discussion',
        'commentXarr_settings_section'
    );
}

/**
 * 注册 初始化函数 到 admin_init Action 钩子
 */
add_action('admin_init', 'commentXarr_initFunction');
add_action('wp_insert_comment', 'commentXarr_commentInsertedTrigger', 10, 2);

function commentXarr_commentInsertedTrigger($comment_id, $comment_object)
{
    $apiUrl = get_option('xarrnotify_api');
    if (isset($apiUrl)) {
        $url = $apiUrl;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');     // 请求方式
        curl_setopt($ch, CURLOPT_POST, true);                // post提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'content'              => (string)$comment_object->comment_content,
            'comment_author'       => (string)$comment_object->comment_author,
            'comment_author_email' => (string)$comment_object->comment_author_email,
            'comment_author_ip'    => (string)$comment_object->comment_author_IP,
            'comment_id'           => (string)$comment_id,
            'post_id'              => (string)$comment_object->comment_post_ID,
            'comment_date'         => (string)$comment_object->comment_date,
            'title'                => get_the_title($comment_object->comment_post_ID),
        ]));              // post的变量

        // 请求头，可以传数组
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         // 执行后不直接打印出来
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        // 不从证书中检查SSL加密算法是否存在
        $output = curl_exec($ch);                               //执行并获取HTML文档内容
        curl_close($ch);                                        //释放curl句柄
    }
}

/**
 * 回调函数
 */
// 分节内容回调
function commentXarr_settings_section_cb()
{
    echo '
    <p>说明: 保存Xarr-Notify通知连接后,方可推送到服务中</p>
    <p><a href="https://xarr-notify.52nyg.com">XArr-Notify</a>获取通知链接</p>
    <p>栗子: https://xarr-notify.52nyg.com/notice?type=wordpress</p>
    <p>QQ群: 996973766</p>';
}

// 字段内容回调
function commentXarr_settings_field_cb()
{
    // 获取我们使用 register_setting() 注册的字段的值
    $setting = get_option('xarrnotify_api');
    // 输出字段
    $val = isset($setting) ? esc_attr($setting) : '';
    echo '<input type=text name="xarrnotify_api" value="' . $val . '" >';
}
