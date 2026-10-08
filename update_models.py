import re

models_data = {
    'Coupon': ['code', 'discount_type', 'discount_value', 'min_order_amount', 'target_universe', 'max_uses', 'used_count', 'is_active', 'expires_at', 'revenue_generated'],
    'AdCampaign': ['name', 'universe', 'status', 'daily_budget', 'spend_today', 'platform_roas', 'net_real_roas', 'cpa', 'ctr', 'fatigue_alert', 'campaign_id_external'],
    'LeadInteraction': ['lead_id', 'channel', 'interaction_type', 'summary', 'payload'],
    'ChatbotConversation': ['channel', 'customer_name', 'session_status', 'messages_json']
}

for model, fields in models_data.items():
    path = f'app/Models/{model}.php'
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    fillable_str = "    protected $fillable = [\n        '" + "', '".join(fields) + "'\n    ];"
    content = re.sub(r'protected \$guarded\s*=\s*\[.*?\];', fillable_str, content)
    
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

# Update Lead model which has missing fillable fields
lead_path = 'app/Models/Lead.php'
with open(lead_path, 'r', encoding='utf-8') as f:
    lead_content = f.read()

lead_content = lead_content.replace(
    "'name', 'email', 'phone', 'source', 'status', 'notes', 'metadata', 'last_contacted_at',",
    "'name', 'email', 'phone', 'source', 'status', 'notes', 'metadata', 'last_contacted_at', 'universe', 'lifecycle_stage', 'lead_score', 'last_interaction_at'"
)

with open(lead_path, 'w', encoding='utf-8') as f:
    f.write(lead_content)

print("Models updated with strict $fillable!")

