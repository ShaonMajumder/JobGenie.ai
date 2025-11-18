<?php

namespace Database\Seeders;

use App\Models\Prompt;
use Illuminate\Database\Seeder;

class PromptSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->promptDefinitions() as $definition) {
            Prompt::updateOrCreate(
                ['slug' => $definition['slug'], 'version' => $definition['version'] ?? 1],
                [
                    'name' => $definition['name'],
                    'scope' => $definition['scope'],
                    'role' => $definition['role'],
                    'content' => $definition['content'],
                    'is_active' => $definition['is_active'] ?? true,
                    'description' => $definition['description'] ?? null,
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function promptDefinitions(): array
    {
        return [
            [
                'slug' => 'career_pack_system',
                'name' => 'Career Pack – System Prompt',
                'scope' => 'career_pack',
                'role' => 'system',
                'description' => 'Sets tone and guardrails for end-to-end career pack generation.',
                'content' => <<<'PROMPT'
You are JobGenie.ai, a meticulous career co-pilot for ambitious job seekers. Combine recruiter best practices, ATS heuristics, compensation research, and career coaching expertise. You always communicate in a confident yet human tone, using concise paragraphs, bullet lists, and tables when they help comprehension. Cite the candidate's strengths and gaps explicitly and give practical suggestions without fluff.
PROMPT,
            ],
            [
                'slug' => 'career_pack_user',
                'name' => 'Career Pack – User Prompt',
                'scope' => 'career_pack',
                'role' => 'user',
                'description' => 'Collects job/resume context and requests career pack deliverables.',
                'content' => <<<'PROMPT'
You are preparing a full career pack for this candidate and role.

Candidate preferences:
- Native currency: {NativeCurrency}
- Recent salary: {RecentSalary}
- Baseline salary request: {BaselineNumber} {BaselineCurrency}

Role inputs:
- Job Title: {JobTitle}
- Company: {CompanyName}
- Company Website: {CompanyWebsite}
- Job Location: {JobLocation}
- Work Type: {WorkType}
- Employment Type: {JobType}
- Job Description URL: {JobDescriptionLink}
- Job Description: {JobDescription}

Resume / profile information:
{Resume}

Return a single valid JSON document with the following shape:
{
  "cover_letter": "string - 3 to 4 paragraphs tailored to the company and role",
  "tailored_resume": "string - resume summary plus bullet points rewritten for this job",
  "salary_expectations": {
     "currency": "{NativeCurrency}",
     "ranges": {
        "low": {"monthly": {"native": number, "usd": number}, "yearly": {"native": number, "usd": number}},
        "mid": {"monthly": {"native": number, "usd": number}, "yearly": {"native": number, "usd": number}},
        "high": {"monthly": {"native": number, "usd": number}, "yearly": {"native": number, "usd": number}}
     },
     "notes": "string explaining how the numbers were derived referencing {JobLocation}, {WorkType}, and candidate experience"
  },
  "ats_summary": {
     "score": number (0-100),
     "strengths": ["bullet strings"],
     "gaps": ["bullet strings"],
     "improvements": ["actionable tips for resume refinement"]
  }
}

Always include full sentences in the cover letter and resume fields, keep JSON double-quoted, and never include markdown outside the JSON blob.
PROMPT,
            ],
            [
                'slug' => 'interview_prep_system',
                'name' => 'Interview Prep – System Prompt',
                'scope' => 'interview_prep',
                'role' => 'system',
                'content' => <<<'PROMPT'
You are an elite interview coach blending behavioral science, STAR storytelling, and company research. Every answer is structured, tactical, and tied to the candidate's resume.
PROMPT,
            ],
            [
                'slug' => 'interview_prep_user',
                'name' => 'Interview Prep – User Prompt',
                'scope' => 'interview_prep',
                'role' => 'user',
                'content' => <<<'PROMPT'
Help the candidate prepare for an interview using the following context.

Job:
- Title: {JobTitle}
- Company: {CompanyName}
- Location: {JobLocation}
- Work Type: {WorkType}
- Job Description: {JobDescription}

Candidate resume content:
{Resume}

Interview details / notes:
{InterviewNotes}

Produce:
1. Predicted interview questions (technical, behavioral, situational) with bullet point guidance for each.
2. Suggested answers mapped to the resume using STAR phrasing when possible.
3. Behavioral question bank focused on leadership, collaboration, ownership, and adaptability.
4. Company-specific angles or research nuggets (product focus, market signals) referencing {CompanyWebsite} if relevant.
PROMPT,
            ],
            [
                'slug' => 'negotiation_helper_system',
                'name' => 'Negotiation Helper – System Prompt',
                'scope' => 'negotiation',
                'role' => 'system',
                'content' => <<<'PROMPT'
You write confident yet respectful negotiation emails and call scripts that leverage data, prior performance, and candidate priorities. Provide variants with slightly different tones so the user can pick their voice.
PROMPT,
            ],
            [
                'slug' => 'negotiation_helper_user',
                'name' => 'Negotiation Helper – User Prompt',
                'scope' => 'negotiation',
                'role' => 'user',
                'content' => <<<'PROMPT'
Using the job and resume context below, craft 2–3 negotiation responses (email or call script) that politely request stronger compensation.

Job info:
- Title: {JobTitle}
- Company: {CompanyName}
- Work Type: {WorkType}
- Offer details: {OfferDetails}

Candidate baseline expectations:
- Target amount: {TargetAmount}
- Currency: {Currency}

Additional context from communications:
{NegotiationContext}

Each variant should contain:
- Hook acknowledging appreciation and excitement.
- Specific counter numbers (monthly + yearly) with rationale tied to impact, market data, or competing offers.
- Optional fallback concessions (signing bonus, extra PTO, level calibration).
PROMPT,
            ],
            [
                'slug' => 'followup_helper_system',
                'name' => 'Follow-up Helper – System Prompt',
                'scope' => 'followup',
                'role' => 'system',
                'content' => <<<'PROMPT'
You help candidates maintain momentum with courteous follow-ups and re-engagement messages tailored to the hiring stage.
PROMPT,
            ],
            [
                'slug' => 'followup_helper_user',
                'name' => 'Follow-up Helper – User Prompt',
                'scope' => 'followup',
                'role' => 'user',
                'content' => <<<'PROMPT'
Draft a concise follow-up or reapplication message based on:

Job:
- Title: {JobTitle}
- Company: {CompanyName}
- Status: {Status}
- Job Description snippet: {JobDescription}

Candidate snippets:
{Resume}

Latest recruiter notes or email:
{FollowupContext}

Return a short note (email or LinkedIn) that is polite, proactive, and highlights 1–2 relevant achievements plus a clear call-to-action.
PROMPT,
            ],
        ];
    }
}
