<?php
// 通义千问大模型配置
return [
    // API密钥
    'api_key' => 'sk-3e2ea0cf9b154885a6129dbe0decfa61',
    
    // 应用ID
    'app_id' => 'c948c6fa411a40b899d96eba5425f8b5',
    
    // 选用模型
    'model' => 'qwen-turbo-0919',
    
    // API基础URL
    'base_url' => 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation',
    
    // 默认参数
    'default_params' => [
        'max_tokens' => 1500,
        'temperature' => 0.7,
        'top_p' => 0.8,
    ],
    
    // 超时设置
    'timeout' => 30,
];