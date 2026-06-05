// Utilisateur
export interface User {
  id: string;
  email: string;
  first_name: string;
  last_name: string;
  phone: string | null;
  role: "client" | "freelance" | "admin";
  status: "active" | "suspended" | "banned";
  avatar_url: string | null;
  locale: string;
  email_verified_at: string | null;
  phone_verified_at: string | null;
  last_login_at: string | null;
  created_at: string;
  updated_at: string;
}

// Profil
export interface Profile {
  id: string;
  user_id: string;
  display_name: string | null;
  bio: string | null;
  title: string | null;
  country: string | null;
  city: string | null;
  gender: string | null;
  birth_date: string | null;
  website_url: string | null;
  linkedin_url: string | null;
  github_url: string | null;
  phone_secondary: string | null;
  is_visible: boolean;
}

export interface ClientProfile {
  id: string;
  user_id: string;
  company_name: string | null;
  company_website: string | null;
  company_size: string | null;
  industry: string | null;
  total_projects_posted: number;
  total_spent: number;
  average_rating: number | null;
}

export interface FreelanceProfile {
  id: string;
  user_id: string;
  professional_title: string | null;
  tagline: string | null;
  experience_level: "junior" | "intermediate" | "senior" | "expert" | null;
  years_experience: number | null;
  education_level: string | null;
  hourly_rate_min: number | null;
  hourly_rate_max: number | null;
  daily_rate_xof: number | null;
  currency: string;
  is_available: boolean;
  availability_note: string | null;
  total_projects_completed: number;
  total_projects_in_progress: number;
  average_rating: number | null;
  total_reviews: number;
  total_earnings: number;
  success_rate: number | null;
  response_rate: number | null;
  last_active_at: string | null;
  skills?: FreelanceSkill[];
  portfolio?: PortfolioItem[];
}

export interface FreelanceSkill {
  skill_id: string;
  proficiency_level: "beginner" | "intermediate" | "advanced" | "expert";
  years_experience: number | null;
  skill: Skill;
}

// Catégories et compétences
export interface JobCategory {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  icon_url: string | null;
  color: string | null;
  sort_order: number;
  is_active: boolean;
  parent_id: string | null;
  children?: JobCategory[];
  skills?: Skill[];
}

export interface Skill {
  id: string;
  name: string;
  category_id: string;
  is_active: boolean;
}

// Projets
export interface Project {
  id: string;
  client_id: string;
  category_id: string;
  title: string;
  description: string;
  status: "open" | "in_progress" | "completed" | "cancelled";
  budget_min: number | null;
  budget_max: number | null;
  currency: string;
  experience_level: string | null;
  duration_days: number | null;
  required_skills: string[];
  project_type: string | null;
  is_featured: boolean;
  is_urgent: boolean;
  is_remote: boolean;
  location: string | null;
  quotes_count: number;
  views_count: number;
  published_at: string | null;
  deadline_at: string | null;
  created_at: string;
  client?: User;
  category?: JobCategory;
  files?: ProjectFile[];
}

export interface ProjectFile {
  id: string;
  project_id: string;
  file_url: string;
  file_name: string;
  file_type: string;
  file_size: number;
}

// Devis
export interface Quote {
  id: string;
  project_id: string;
  freelance_id: string;
  amount: number;
  currency: string;
  estimated_duration: number | null;
  proposal: string;
  cover_letter: string | null;
  status: "pending" | "accepted" | "refused" | "withdrawn";
  created_at: string;
  freelance?: User & { freelance_profile?: FreelanceProfile };
}

// Contrats
export interface Contract {
  id: string;
  project_id: string;
  quote_id: string;
  client_id: string;
  freelance_id: string;
  title: string;
  description: string | null;
  total_amount: number;
  currency: string;
  platform_fee: number;
  commission_rate: number;
  commission_xof: number;
  freelance_amount: number;
  start_date: string | null;
  end_date: string | null;
  status: "pending" | "active" | "completed" | "cancelled" | "disputed";
  client_signed_at: string | null;
  freelance_signed_at: string | null;
  created_at: string;
  milestones?: Milestone[];
  project?: Project;
  client?: User;
  freelance?: User;
}

export interface Milestone {
  id: string;
  contract_id: string;
  title: string;
  description: string | null;
  amount: number;
  due_date: string | null;
  sort_order: number;
  is_completed: boolean;
  delivered_at: string | null;
  validated_at: string | null;
  completed_at: string | null;
}

// Paiements
export interface Payment {
  id: string;
  contract_id: string;
  payer_id: string;
  payee_id: string;
  amount: number;
  currency: string;
  status: string;
  payment_channel: string | null;
  reference: string | null;
  created_at: string;
}

export interface Wallet {
  id: string;
  user_id: string;
  available_xof: number;
  pending_xof: number;
  total_earned_xof: number;
  total_withdrawn_xof: number;
  currency: string;
}

export interface WalletTransaction {
  id: string;
  wallet_id: string;
  type: string;
  direction: "credit" | "debit";
  amount_xof: number;
  balance_before_xof: number;
  balance_after_xof: number;
  description: string;
  created_at: string;
}

// Messagerie
export interface Conversation {
  id: string;
  project_id: string | null;
  contract_id: string | null;
  client_id: string;
  freelance_id: string;
  subject: string | null;
  last_message_at: string | null;
  messages?: Message[];
  client?: User;
  freelance?: User;
}

export interface Message {
  id: string;
  conversation_id: string;
  sender_id: string;
  content: string;
  status: "sent" | "read";
  read_at: string | null;
  created_at: string;
  sender?: User;
}

// Avis
export interface Review {
  id: string;
  contract_id: string;
  reviewer_id: string;
  reviewee_id: string;
  rating: number;
  rating_quality: number | null;
  rating_delay: number | null;
  rating_communication: number | null;
  comment: string | null;
  is_public: boolean;
  created_at: string;
  reviewer?: User;
  reply?: ReviewReply;
}

export interface ReviewReply {
  id: string;
  review_id: string;
  user_id: string;
  comment: string;
  created_at: string;
}

// Portfolio
export interface PortfolioItem {
  id: string;
  freelance_profile_id: string;
  title: string;
  description: string | null;
  project_url: string | null;
  completed_date: string | null;
  is_featured: boolean;
  sort_order: number;
  files?: PortfolioFile[];
}

export interface PortfolioFile {
  id: string;
  portfolio_item_id: string;
  file_url: string;
  file_type: string;
  file_size: number;
}

// Notifications
export interface Notification {
  id: string;
  user_id: string;
  type: string;
  title: string;
  body: string | null;
  data: Record<string, unknown> | null;
  action_url: string | null;
  is_read: boolean;
  read_at: string | null;
  created_at: string;
}

// Abonnements
export interface SubscriptionPlan {
  id: string;
  plan: "starter" | "pro" | "expert";
  name: string;
  description: string | null;
  price_monthly: number;
  price_yearly: number;
  features: string[];
  is_active: boolean;
  sort_order: number;
}

export interface FreelanceSubscription {
  id: string;
  freelance_profile_id: string;
  plan_id: string;
  status: "active" | "cancelled" | "expired" | "trial";
  started_at: string;
  ends_at: string | null;
  billing_cycle: string;
  auto_renew: boolean;
}

// Badges et Boosts
export interface VerifiedBadge {
  id: string;
  freelance_profile_id: string;
  badge_type: string;
  is_active: boolean;
  granted_at: string;
  expires_at: string | null;
}

export interface Boost {
  id: string;
  target: "profile" | "project";
  target_id: string | null;
  duration: string;
  is_active: boolean;
  started_at: string;
  ends_at: string;
}

// Réponses API
export interface ApiResponse<T> {
  message: string;
  data: T;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ValidationError {
  message: string;
  errors: Record<string, string[]>;
}

export interface Report {
  id: string;
  reporter_id: string;
  reported_user_id: string | null;
  reported_project_id: string | null;
  type: string;
  description: string;
  evidence: Record<string, unknown> | null;
  status: "open" | "resolved" | "dismissed";
  admin_notes: string | null;
  reviewed_by: string | null;
  reviewed_at: string | null;
  created_at: string;
  reporter?: User;
  reported_user?: User;
}

export interface Dispute {
  id: string;
  contract_id: string;
  raised_by: string;
  reason: string;
  evidence: Record<string, unknown> | null;
  status: "open" | "resolved" | "dismissed";
  admin_notes: string | null;
  resolved_by: string | null;
  resolved_at: string | null;
  created_at: string;
  contract?: Contract;
}

export interface Verification {
  id: string;
  user_id: string;
  type: string;
  document_url: string | null;
  status: "pending" | "approved" | "rejected";
  admin_notes: string | null;
  reviewed_by: string | null;
  reviewed_at: string | null;
  created_at: string;
  user?: User;
}

export interface PlatformSetting {
  id: string;
  key: string;
  value: string;
  group: string | null;
  type: string;
  description: string | null;
  is_public: boolean;
}

export interface WithdrawalRequest {
  id: string;
  wallet_id: string;
  user_id: string;
  amount: number;
  fee: number;
  net_amount: number;
  phone_number: string | null;
  withdrawal_method: string;
  status: "pending" | "approved" | "completed" | "rejected";
  admin_notes: string | null;
  created_at: string;
}
