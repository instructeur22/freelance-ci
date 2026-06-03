<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Platform settings
        $settings = [
            ['key' => 'site_name', 'value' => 'Freelance CI', 'group' => 'general', 'type' => 'string', 'description' => 'Nom du site'],
            ['key' => 'site_description', 'value' => 'Plateforme de mise en relation entre freelances et clients en Côte d\'Ivoire', 'group' => 'general', 'type' => 'string', 'description' => 'Description du site'],
            ['key' => 'contact_email', 'value' => 'contact@freelance-ci.com', 'group' => 'general', 'type' => 'string', 'description' => 'Email de contact'],
            ['key' => 'support_email', 'value' => 'support@freelance-ci.com', 'group' => 'general', 'type' => 'string', 'description' => 'Email de support'],
            ['key' => 'platform_fee_percentage', 'value' => '10', 'group' => 'payment', 'type' => 'integer', 'description' => 'Commission plateforme en pourcentage'],
            ['key' => 'platform_fee_fixed', 'value' => '500', 'group' => 'payment', 'type' => 'integer', 'description' => 'Frais fixe plateforme en XOF'],
            ['key' => 'min_withdrawal_amount', 'value' => '5000', 'group' => 'payment', 'type' => 'integer', 'description' => 'Montant minimum de retrait'],
            ['key' => 'max_withdrawal_amount', 'value' => '1000000', 'group' => 'payment', 'type' => 'integer', 'description' => 'Montant maximum de retrait'],
            ['key' => 'withdrawal_fee_percentage', 'value' => '1.5', 'group' => 'payment', 'type' => 'decimal', 'description' => 'Frais de retrait en pourcentage'],
            ['key' => 'currency_default', 'value' => 'XOF', 'group' => 'general', 'type' => 'string', 'description' => 'Devise par défaut'],
            ['key' => 'trial_duration_days', 'value' => '14', 'group' => 'subscription', 'type' => 'integer', 'description' => 'Durée de la période d\'essai en jours'],
            ['key' => 'max_projects_free', 'value' => '3', 'group' => 'subscription', 'type' => 'integer', 'description' => 'Nombre max de projets pour les comptes gratuits'],
            ['key' => 'max_quotes_free', 'value' => '5', 'group' => 'subscription', 'type' => 'integer', 'description' => 'Nombre max de devis par mois pour les comptes gratuits'],
            ['key' => 'genius_pay_api_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'Clé API Genius Pay', 'is_public' => false],
            ['key' => 'genius_pay_merchant_id', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'ID Marchand Genius Pay', 'is_public' => false],
            ['key' => 'genius_pay_webhook_secret', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'Secret Webhook Genius Pay', 'is_public' => false],
            ['key' => 'genius_pay_mode', 'value' => 'sandbox', 'group' => 'payment', 'type' => 'string', 'description' => 'Mode Genius Pay (sandbox/production)'],
            ['key' => 'boost_profile_price_7_days', 'value' => '5000', 'group' => 'boost', 'type' => 'integer', 'description' => 'Prix boost profil 7 jours'],
            ['key' => 'boost_profile_price_30_days', 'value' => '15000', 'group' => 'boost', 'type' => 'integer', 'description' => 'Prix boost profil 30 jours'],
            ['key' => 'boost_project_price_7_days', 'value' => '3000', 'group' => 'boost', 'type' => 'integer', 'description' => 'Prix boost projet 7 jours'],
            ['key' => 'boost_project_price_30_days', 'value' => '10000', 'group' => 'boost', 'type' => 'integer', 'description' => 'Prix boost projet 30 jours'],
            ['key' => 'max_boost_per_profile', 'value' => '3', 'group' => 'boost', 'type' => 'integer', 'description' => 'Nombre max de boosts simultanés par profil'],
            ['key' => 'badge_verified_price', 'value' => '25000', 'group' => 'verification', 'type' => 'integer', 'description' => 'Prix du badge vérifié'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'general', 'type' => 'boolean', 'description' => 'Mode maintenance'],
            ['key' => 'registration_enabled', 'value' => 'true', 'group' => 'general', 'type' => 'boolean', 'description' => 'Inscriptions ouvertes'],
            ['key' => 'max_file_size_mb', 'value' => '10', 'group' => 'general', 'type' => 'integer', 'description' => 'Taille max des fichiers en MB'],
            ['key' => 'allowed_file_types', 'value' => 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip', 'group' => 'general', 'type' => 'string', 'description' => 'Types de fichiers autorisés'],
            ['key' => 'auto_approve_profiles', 'value' => 'false', 'group' => 'general', 'type' => 'boolean', 'description' => 'Approbation automatique des profils'],
            ['key' => 'referral_reward_amount', 'value' => '5000', 'group' => 'referral', 'type' => 'integer', 'description' => 'Montant de la récompense de parrainage (XOF)'],
        ];

        foreach ($settings as $setting) {
            $setting['id'] = (string) Str::uuid();
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            if (!isset($setting['is_public'])) {
                $setting['is_public'] = true;
            }
            DB::table('platform_settings')->insert($setting);
        }

        // Job categories
        $categories = [
            ['name' => 'Développement Web', 'slug' => 'developpement-web', 'description' => 'Création de sites web, applications web et API', 'icon' => 'code', 'color' => '#3B82F6', 'sort_order' => 1],
            ['name' => 'Design Graphique', 'slug' => 'design-graphique', 'description' => 'Logo, charte graphique, maquettes UI/UX', 'icon' => 'palette', 'color' => '#EC4899', 'sort_order' => 2],
            ['name' => 'Rédaction & Traduction', 'slug' => 'redaction-traduction', 'description' => 'Contenu web, articles, traduction de documents', 'icon' => 'edit', 'color' => '#10B981', 'sort_order' => 3],
            ['name' => 'Marketing Digital', 'slug' => 'marketing-digital', 'description' => 'SEO, SEA, social media, email marketing', 'icon' => 'trending-up', 'color' => '#F59E0B', 'sort_order' => 4],
            ['name' => 'Vidéo & Animation', 'slug' => 'video-animation', 'description' => 'Montage vidéo, motion design, 3D', 'icon' => 'video', 'color' => '#EF4444', 'sort_order' => 5],
            ['name' => 'Mobile', 'slug' => 'mobile', 'description' => 'Applications iOS et Android', 'icon' => 'smartphone', 'color' => '#8B5CF6', 'sort_order' => 6],
            ['name' => 'Réseaux & Sécurité', 'slug' => 'reseaux-securite', 'description' => 'Installation réseau, cybersécurité, audit', 'icon' => 'shield', 'color' => '#DC2626', 'sort_order' => 7],
            ['name' => 'Data & IA', 'slug' => 'data-ia', 'description' => 'Analyse de données, machine learning, IA', 'icon' => 'database', 'color' => '#6366F1', 'sort_order' => 8],
            ['name' => 'Assistance Virtuelle', 'slug' => 'assistance-virtuelle', 'description' => 'Assistant administratif, gestion de projet', 'icon' => 'headphones', 'color' => '#14B8A6', 'sort_order' => 9],
            ['name' => 'Conseil & Expertise', 'slug' => 'conseil-expertise', 'description' => 'Consulting, audit, accompagnement', 'icon' => 'briefcase', 'color' => '#F97316', 'sort_order' => 10],
        ];

        foreach ($categories as $cat) {
            $cat['id'] = (string) Str::uuid();
            $cat['is_active'] = true;
            $cat['created_at'] = now();
            $cat['updated_at'] = now();
            DB::table('job_categories')->insert($cat);
        }

        // Skills for each category
        $skillsByCategory = [
            'developpement-web' => ['PHP/Laravel', 'JavaScript/React', 'Python/Django', 'Node.js', 'WordPress', 'Vue.js', 'Angular', 'TypeScript', 'HTML/CSS', 'API REST'],
            'design-graphique' => ['Logo Design', 'UI/UX Design', 'Charte Graphique', 'Photoshop', 'Illustrator', 'Figma', 'Canva', 'Branding'],
            'redaction-traduction' => ['Rédaction Web', 'Copywriting', 'Traduction Anglais', 'Traduction Français', 'Correction', 'Relecture'],
            'marketing-digital' => ['SEO', 'Google Ads', 'Social Media', 'Email Marketing', 'Community Management', 'Analytics'],
            'video-animation' => ['Montage Vidéo', 'Motion Design', 'After Effects', 'Premiere Pro', 'Animation 3D', 'Blender'],
            'mobile' => ['Flutter', 'React Native', 'iOS/Swift', 'Android/Kotlin', 'Hybrid Apps'],
            'reseaux-securite' => ['Réseaux', 'Cybersécurité', 'Firewall', 'Cloud Security', 'Audit Sécurité'],
            'data-ia' => ['Data Analysis', 'Machine Learning', 'Python', 'SQL', 'Power BI', 'Tableau', 'Big Data'],
            'assistance-virtuelle' => ['Gestion Projet', 'Support Admin', 'Saisie', 'CRM', 'Calendar Management'],
            'conseil-expertise' => ['Consulting IT', 'Audit', 'Formation', 'Stratégie Digitale', 'Accompagnement'],
        ];

        foreach ($skillsByCategory as $slug => $skills) {
            $category = DB::table('job_categories')->where('slug', $slug)->first();
            if ($category) {
                foreach ($skills as $skillName) {
                    $skillSlug = Str::slug($skillName);
                    DB::table('skills')->insert([
                        'id' => (string) Str::uuid(),
                        'name' => $skillName,
                        'slug' => $skillSlug,
                        'category_id' => $category->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Subscription plans config
        $plans = [
            [
                'plan' => 'starter', 'name' => 'Starter', 'description' => 'Pour démarrer votre activité freelance',
                'price_monthly' => 0, 'price_yearly' => 0,
                'max_projects' => 3, 'max_quotes_per_month' => 5,
                'has_verified_badge' => false, 'has_boost_option' => false,
                'features' => json_encode(['Profil de base', '3 projets', '5 devis/mois', 'Support email']),
                'sort_order' => 1,
            ],
            [
                'plan' => 'pro', 'name' => 'Pro', 'description' => 'Pour les freelances professionnels',
                'price_monthly' => 15000, 'price_yearly' => 150000,
                'max_projects' => 20, 'max_quotes_per_month' => 50,
                'has_verified_badge' => false, 'has_boost_option' => true,
                'features' => json_encode(['Profil avancé', '20 projets', '50 devis/mois', 'Boost option', 'Support prioritaire']),
                'sort_order' => 2,
            ],
            [
                'plan' => 'expert', 'name' => 'Expert', 'description' => 'Pour les experts et agences',
                'price_monthly' => 35000, 'price_yearly' => 350000,
                'max_projects' => null, 'max_quotes_per_month' => null,
                'has_verified_badge' => true, 'has_boost_option' => true,
                'features' => json_encode(['Profil premium', 'Projets illimités', 'Devis illimités', 'Badge vérifié', 'Boost prioritaire', 'Support VIP']),
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            $plan['id'] = (string) Str::uuid();
            $plan['is_active'] = true;
            $plan['created_at'] = now();
            $plan['updated_at'] = now();
            DB::table('subscription_plans_config')->insert($plan);
        }
    }

    public function down(): void
    {
        DB::table('subscription_plans_config')->whereIn('plan', ['starter', 'pro', 'expert'])->delete();
        DB::table('skills')->delete();
        DB::table('job_categories')->delete();
        DB::table('platform_settings')->delete();
    }
};
