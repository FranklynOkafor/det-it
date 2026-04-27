<?php

namespace DetIt\Admin\Onboarding;

if (!defined('ABSPATH')) {
    exit;
}

return [
    'store_name' => [
        'type' => 'text',
        'label' => 'Store Name',
    ],
    'store_url' => [
        'type' => 'text',
        'label' => 'Store URL',
        'default' => get_site_url(),
    ],
    'target_market' => [
        'type' => 'select_with_other',
        'label' => 'Target Market',
        'options' => [
            'global' => 'Global',
            'nigeria' => 'Nigeria',
            'usa' => 'USA',
            'uk' => 'UK',
            'canada' => 'Canada',
            'other' => 'Other',
        ],
    ],
    'store_type' => [
        'type' => 'select',
        'label' => 'Store Type',
        'options' => [
            'physical' => 'Physical',
            'digital' => 'Digital',
            'hybrid' => 'Hybrid',
        ],
    ],
    'industry_niche' => [
        'type' => 'select_with_other',
        'label' => 'Industry/Niche',
        'options' => [
            'fashion' => 'Fashion',
            'electronics' => 'Electronics',
            'beauty' => 'Beauty',
            'home' => 'Home',
            'fitness' => 'Fitness',
            'food' => 'Food',
            'digital_products' => 'Digital Products',
            'services' => 'Services',
            'other' => 'Other',
        ],
    ],
    'target_audience_type' => [
        'type' => 'select_with_other',
        'label' => 'Target Audience Type',
        'options' => [
            'general_consumers' => 'General Consumers',
            'professionals' => 'Professionals',
            'students' => 'Students',
            'businesses' => 'Businesses',
            'niche_specific' => 'Niche Specific',
            'other' => 'Other',
        ],
    ],
    'target_audience_detail' => [
        'type' => 'text',
        'label' => 'Target Audience Detail',
    ],
    'primary_goal' => [
        'type' => 'select',
        'label' => 'Primary Goal',
        'options' => [
            'traffic' => 'Traffic',
            'conversions' => 'Conversions',
            'ranking_improvement' => 'Ranking Improvement',
            'technical_cleanup' => 'Technical Cleanup',
        ],
    ],
    'content_tone' => [
        'type' => 'select',
        'label' => 'Content Tone',
        'options' => [
            'professional' => 'Professional',
            'casual' => 'Casual',
            'persuasive' => 'Persuasive',
            'technical' => 'Technical',
        ],
    ],
];
