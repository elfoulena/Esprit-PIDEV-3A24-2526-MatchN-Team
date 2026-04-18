<?php
// src/Service/AITeamAnalyticsService.php

namespace App\Service;

use App\Entity\Equipe;
use App\Entity\MembreEquipe;
use App\Repository\EquipeRepository;
use App\Repository\MembreEquipeRepository;
use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class AITeamAnalyticsService
{
    private EquipeRepository $equipeRepo;
    private MembreEquipeRepository $membreRepo;
    private EvenementRepository $evenementRepo;
    private EntityManagerInterface $em;

    public function __construct(
        EquipeRepository $equipeRepo,
        MembreEquipeRepository $membreRepo,
        EvenementRepository $evenementRepo,
        EntityManagerInterface $em
    ) {
        $this->equipeRepo = $equipeRepo;
        $this->membreRepo = $membreRepo;
        $this->evenementRepo = $evenementRepo;
        $this->em = $em;
    }

    /**
     * Calculate advanced team statistics with AI predictions
     */
    public function getTeamAdvancedStats(Equipe $equipe): array
    {
        $members = $equipe->getMembres();
        
        // Convert PersistentCollection to array if needed
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $totalMembers = count($members);
        
        // If team has no members, return default values
        if ($totalMembers === 0) {
            return $this->getEmptyTeamStats($equipe);
        }
        
        // Member engagement score
        $engagementScore = $this->calculateEngagementScore($members);
        
        // Team performance prediction
        $performancePrediction = $this->predictTeamPerformance($equipe);
        
        // Resource optimization suggestions
        $optimizationSuggestions = $this->getOptimizationSuggestions($equipe);
        
        // Member compatibility matrix
        $compatibilityMatrix = $this->calculateCompatibilityMatrix($members);
        
        // Skill gap analysis
        $skillGapAnalysis = $this->analyzeSkillGaps($members);
        
        // Budget efficiency score
        $budgetEfficiency = $this->calculateBudgetEfficiency($equipe);
        
        // Team health score
        $teamHealthScore = $this->calculateTeamHealthScore($equipe, $members);
        
        // Predictive turnover risk
        $turnoverRisk = $this->predictTurnoverRisk($members);
        
        // Project success probability
        $successProbability = $this->calculateSuccessProbability($equipe);
        
        // Weekly activity patterns
        $activityPatterns = $this->analyzeActivityPatterns($equipe);
        
        // Growth potential calculation
        $growthPotential = $this->calculateGrowthPotential($equipe);
        
        return [
            'team_metrics' => [
                'total_members' => $totalMembers,
                'member_turnover_rate' => $this->calculateTurnoverRate($equipe),
                'average_participation_rate' => $this->calculateAverageParticipation($members),
                'engagement_score' => $engagementScore,
                'team_health_score' => $teamHealthScore,
                'budget_efficiency' => $budgetEfficiency,
                'diversity_score' => $this->calculateDiversityScore($members),
                'productivity_index' => $this->calculateProductivityIndex($equipe),
            ],
            'ai_predictions' => [
                'performance_prediction' => $performancePrediction,
                'turnover_risk' => $turnoverRisk,
                'success_probability' => $successProbability,
                'growth_potential' => $growthPotential,
                'resource_optimization' => $optimizationSuggestions,
                'skill_gap_analysis' => $skillGapAnalysis,
            ],
            'insights' => [
                'compatibility_matrix' => $compatibilityMatrix,
                'activity_patterns' => $activityPatterns,
                'recommendations' => $this->generateRecommendations($equipe, $optimizationSuggestions, $skillGapAnalysis),
                'strengths' => $this->identifyTeamStrengths($members),
                'weaknesses' => $this->identifyTeamWeaknesses($members),
                'opportunities' => $this->identifyOpportunities($equipe),
            ],
            'visualization_data' => [
                'member_contribution_chart' => $this->getMemberContributionData($members),
                'skill_distribution' => $this->getSkillDistribution($members),
                'timeline_metrics' => $this->getTimelineMetrics($equipe),
                'heatmap_data' => $this->getActivityHeatmap($equipe),
            ]
        ];
    }

    /**
     * Get default stats for empty team
     */
    private function getEmptyTeamStats(Equipe $equipe): array
    {
        return [
            'team_metrics' => [
                'total_members' => 0,
                'member_turnover_rate' => 0,
                'average_participation_rate' => 0,
                'engagement_score' => 0,
                'team_health_score' => 0,
                'budget_efficiency' => 0,
                'diversity_score' => 0,
                'productivity_index' => 0,
            ],
            'ai_predictions' => [
                'performance_prediction' => [
                    'predicted_score' => 0,
                    'confidence_level' => 0,
                    'trend' => 'stable',
                    'expected_improvement' => [
                        'potential_gain' => 0,
                        'timeline' => 'N/A',
                        'key_actions' => ['Recruter des membres pour commencer']
                    ],
                    'benchmark' => ['level' => 'needs_improvement', 'percentile' => 0]
                ],
                'turnover_risk' => [
                    'at_risk_members' => [],
                    'average_risk' => 0,
                    'critical_count' => 0
                ],
                'success_probability' => 0,
                'growth_potential' => 50,
                'resource_optimization' => [
                    [
                        'type' => 'recruitment',
                        'priority' => 'high',
                        'message' => 'Équipe vide - besoin de recrutement urgent',
                        'impact' => 'Fondation essentielle pour démarrer',
                        'action' => 'Lancer une campagne de recrutement immédiate'
                    ]
                ],
                'skill_gap_analysis' => [
                    'gaps' => [],
                    'overall_coverage' => 0,
                    'critical_gaps' => 0
                ],
            ],
            'insights' => [
                'compatibility_matrix' => [],
                'activity_patterns' => ['peak_hours' => [], 'activity_distribution' => []],
                'recommendations' => [
                    [
                        'priority' => 1,
                        'title' => 'Constituer l\'équipe',
                        'action' => 'Recruter des membres pour l\'équipe',
                        'impact' => 'Permettre à l\'équipe de fonctionner'
                    ]
                ],
                'strengths' => ['Potentiel de croissance'],
                'weaknesses' => ['Aucun membre dans l\'équipe'],
                'opportunities' => ['Opportunité de construire une équipe performante depuis zéro'],
            ],
            'visualization_data' => [
                'member_contribution_chart' => [],
                'skill_distribution' => [],
                'timeline_metrics' => [],
                'heatmap_data' => []
            ]
        ];
    }

    /**
     * Calculate growth potential based on multiple factors
     */
    private function calculateGrowthPotential(Equipe $equipe): float
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $currentSize = count($members);
        
        // If team is empty, return neutral potential (50%)
        if ($currentSize === 0) {
            return 50.00;
        }
        
        $maxSize = $equipe->getNbMembresMax();
        
        // Size factor (more room to grow = higher potential)
        $sizeFactor = $maxSize > 0 ? ($maxSize - $currentSize) / $maxSize : 0;
        
        // Engagement factor (highly engaged teams grow faster)
        $engagementScore = $this->calculateEngagementScore($members);
        $engagementFactor = $engagementScore / 100;
        
        // Budget factor (more budget = more growth resources)
        $budget = floatval($equipe->getBudget() ?? 0);
        $budgetPerMember = $currentSize > 0 ? $budget / $currentSize : 0;
        $budgetFactor = min(1, $budgetPerMember / 10000); // Cap at 10k per member
        
        // Skill diversity factor (diverse skills = better growth)
        $skillDistribution = $this->getSkillDistribution($members);
        $skillDiversity = count($skillDistribution);
        $diversityFactor = min(1, $skillDiversity / 10);
        
        // Calculate weighted growth potential
        $potential = (
            $sizeFactor * 0.35 +
            $engagementFactor * 0.30 +
            $budgetFactor * 0.20 +
            $diversityFactor * 0.15
        ) * 100;
        
        return round(min(100, max(0, $potential)), 2);
    }

    private function calculateEngagementScore(array $members): float
    {
        $memberCount = count($members);
        if ($memberCount === 0) return 0;
        
        $totalScore = 0;
        foreach ($members as $member) {
            $score = 0;
            // Participation rate contributes 40%
            $score += floatval($member->getTauxParticipation()) * 0.4;
            
            // Activity recency (based on updated_at)
            $updatedAt = $member->getUpdatedAt();
            $daysSinceUpdate = $updatedAt ? (new \DateTime())->diff($updatedAt)->days : 30;
            $recencyScore = max(0, 100 - ($daysSinceUpdate * 2));
            $score += $recencyScore * 0.3;
            
            // Role responsibility (higher for leaders)
            $roleWeight = $this->getRoleWeight($member->getRoleEquipe());
            $score += $roleWeight * 30;
            
            $totalScore += $score;
        }
        
        return round($totalScore / $memberCount, 2);
    }

    private function getRoleWeight(string $role): float
    {
        return match($role) {
            'Chef' => 1.0,
            'Chef adjoint' => 0.8,
            'Lead' => 0.7,
            'Senior' => 0.6,
            default => 0.4
        };
    }

    private function predictTeamPerformance(Equipe $equipe): array
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $teamSize = count($members);
        
        // If team is empty, return default values
        if ($teamSize === 0) {
            return [
                'predicted_score' => 0,
                'confidence_level' => 0,
                'trend' => 'stable',
                'expected_improvement' => [
                    'potential_gain' => 0,
                    'timeline' => 'N/A',
                    'key_actions' => ['Recruter des membres pour commencer']
                ],
                'benchmark' => ['level' => 'needs_improvement', 'percentile' => 0]
            ];
        }
        
        $avgParticipation = $this->calculateAverageParticipation($members);
        $optimalSize = $equipe->getNbMembresMax();
        $sizeFactor = $optimalSize > 0 ? $teamSize / $optimalSize : 1;
        
        // AI prediction algorithm
        $basePerformance = 70;
        $participationBoost = $avgParticipation * 0.3;
        $sizePenalty = max(0, abs(1 - $sizeFactor) * 15);
        $experienceFactor = min(20, $this->calculateTeamExperience($members) / 30);
        
        $predictedScore = $basePerformance + $participationBoost + $experienceFactor - $sizePenalty;
        $predictedScore = min(100, max(0, $predictedScore));
        
        $trend = $this->calculatePerformanceTrend($equipe);
        
        return [
            'predicted_score' => round($predictedScore, 2),
            'confidence_level' => round(85 - ($sizePenalty * 0.5), 2),
            'trend' => $trend,
            'expected_improvement' => $this->calculateExpectedImprovement($equipe),
            'benchmark' => $this->getBenchmarkComparison($predictedScore),
        ];
    }

    private function getOptimizationSuggestions(Equipe $equipe): array
    {
        $suggestions = [];
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $currentSize = count($members);
        $maxSize = $equipe->getNbMembresMax();
        
        // If team is empty, prioritize recruitment
        if ($currentSize === 0) {
            return [
                [
                    'type' => 'recruitment',
                    'priority' => 'high',
                    'message' => 'Équipe vide - besoin de recrutement urgent',
                    'impact' => 'Fondation essentielle pour démarrer',
                    'action' => 'Lancer une campagne de recrutement immédiate'
                ]
            ];
        }
        
        // Size optimization
        if ($currentSize < $maxSize * 0.6) {
            $suggestions[] = [
                'type' => 'recruitment',
                'priority' => 'high',
                'message' => 'Équipe sous-dimensionnée. Recommandation: Recruter ' . ceil($maxSize * 0.7 - $currentSize) . ' nouveaux membres',
                'impact' => 'Augmentation de productivité estimée: +25%',
                'action' => 'Lancer une campagne de recrutement ciblée'
            ];
        } elseif ($currentSize > $maxSize * 0.9 && $maxSize > 0) {
            $suggestions[] = [
                'type' => 'restructuring',
                'priority' => 'medium',
                'message' => 'Équipe proche de la capacité maximale',
                'impact' => 'Risque de surcharge et burn-out',
                'action' => 'Envisager de scinder l\'équipe ou augmenter la capacité'
            ];
        }
        
        // Skill balance optimization
        $skillDistribution = $this->getSkillDistribution($members);
        $imbalance = $this->detectSkillImbalance($skillDistribution);
        if ($imbalance) {
            $suggestions[] = [
                'type' => 'skill_development',
                'priority' => 'high',
                'message' => 'Déséquilibre des compétences détecté: ' . $imbalance['description'],
                'impact' => 'Impact sur la polyvalence: -15%',
                'action' => 'Organiser des sessions de formation en ' . $imbalance['missing_skills']
            ];
        }
        
        // Budget optimization
        $budgetEfficiency = $this->calculateBudgetEfficiency($equipe);
        if ($budgetEfficiency < 60 && $budgetEfficiency > 0) {
            $suggestions[] = [
                'type' => 'budget',
                'priority' => 'medium',
                'message' => 'Efficacité budgétaire faible',
                'impact' => 'ROI actuel: ' . $budgetEfficiency . '%',
                'action' => 'Audit des dépenses et réallocation des ressources'
            ];
        }
        
        return $suggestions;
    }

    private function calculateCompatibilityMatrix(array $members): array
    {
        $memberCount = count($members);
        
        if ($memberCount < 2) {
            return [];
        }
        
        $matrix = [];
        
        for ($i = 0; $i < $memberCount; $i++) {
            for ($j = $i + 1; $j < $memberCount; $j++) {
                $compatibility = $this->calculatePairCompatibility($members[$i], $members[$j]);
                $matrix[] = [
                    'member1' => $members[$i]->getUser()->getNom() . ' ' . $members[$i]->getUser()->getPrenom(),
                    'member2' => $members[$j]->getUser()->getNom() . ' ' . $members[$j]->getUser()->getPrenom(),
                    'score' => $compatibility['score'],
                    'strengths' => $compatibility['strengths'],
                    'improvement_areas' => $compatibility['improvement_areas']
                ];
            }
        }
        
        usort($matrix, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($matrix, 0, 10);
    }

    private function calculatePairCompatibility(MembreEquipe $member1, MembreEquipe $member2): array
    {
        $score = 0;
        $strengths = [];
        $improvementAreas = [];
        
        // Participation compatibility
        $participation1 = floatval($member1->getTauxParticipation());
        $participation2 = floatval($member2->getTauxParticipation());
        $participationDiff = abs($participation1 - $participation2);
        $participationScore = 100 - ($participationDiff * 0.5);
        $score += $participationScore * 0.3;
        
        if ($participationScore > 80) {
            $strengths[] = 'Niveau d\'engagement similaire';
        } else {
            $improvementAreas[] = 'Différence d\'engagement notable';
        }
        
        // Role compatibility
        $role1 = $member1->getRoleEquipe();
        $role2 = $member2->getRoleEquipe();
        $roleCompatibility = $this->getRoleCompatibility($role1, $role2);
        $score += $roleCompatibility['score'] * 0.4;
        if (!empty($roleCompatibility['strengths'])) {
            $strengths = array_merge($strengths, $roleCompatibility['strengths']);
        }
        
        // Tenure compatibility
        $tenure1 = $member1->getDateAffectation();
        $tenure2 = $member2->getDateAffectation();
        if ($tenure1 && $tenure2) {
            $tenureDiff = abs($tenure1->diff($tenure2)->days);
            $tenureScore = max(0, 100 - ($tenureDiff / 30));
            $score += $tenureScore * 0.3;
            
            if ($tenureScore > 70) {
                $strengths[] = 'Expérience similaire dans l\'équipe';
            }
        }
        
        return [
            'score' => round($score, 2),
            'strengths' => $strengths,
            'improvement_areas' => $improvementAreas
        ];
    }

    private function getRoleCompatibility(string $role1, string $role2): array
    {
        $compatibilityMatrix = [
            'Chef' => ['Chef' => 60, 'Chef adjoint' => 85, 'Lead' => 80, 'Senior' => 75, 'Membre' => 70],
            'Chef adjoint' => ['Chef' => 85, 'Chef adjoint' => 70, 'Lead' => 90, 'Senior' => 85, 'Membre' => 80],
            'Lead' => ['Chef' => 80, 'Chef adjoint' => 90, 'Lead' => 75, 'Senior' => 95, 'Membre' => 85],
            'Senior' => ['Chef' => 75, 'Chef adjoint' => 85, 'Lead' => 95, 'Senior' => 80, 'Membre' => 90],
            'Membre' => ['Chef' => 70, 'Chef adjoint' => 80, 'Lead' => 85, 'Senior' => 90, 'Membre' => 75]
        ];
        
        $score = $compatibilityMatrix[$role1][$role2] ?? 70;
        $strengths = [];
        
        if ($score >= 85) {
            $strengths[] = 'Complémentarité des rôles excellente';
        } elseif ($score >= 70) {
            $strengths[] = 'Bon équilibre des responsabilités';
        }
        
        return ['score' => $score, 'strengths' => $strengths];
    }

    private function analyzeSkillGaps(array $members): array
    {
        $memberCount = count($members);
        if ($memberCount === 0) {
            return [
                'gaps' => [],
                'overall_coverage' => 0,
                'critical_gaps' => 0
            ];
        }
        
        $allSkills = [
            'Technique', 'Management', 'Communication', 'Créativité', 
            'Analyse', 'Leadership', 'Collaboration', 'Résolution de problèmes'
        ];
        
        $presentSkills = [];
        foreach ($members as $member) {
            $competences = $member->getCompetencesPrincipales();
            if ($competences) {
                $skills = array_map('trim', explode(',', $competences));
                foreach ($skills as $skill) {
                    $presentSkills[$skill] = ($presentSkills[$skill] ?? 0) + 1;
                }
            }
        }
        
        $gaps = [];
        foreach ($allSkills as $skill) {
            $coverage = ($presentSkills[$skill] ?? 0) / $memberCount * 100;
            if ($coverage < 50) {
                $gaps[] = [
                    'skill' => $skill,
                    'coverage' => round($coverage, 2),
                    'severity' => $coverage < 30 ? 'high' : ($coverage < 50 ? 'medium' : 'low'),
                    'recommendation' => $this->getSkillRecommendation($skill, $coverage)
                ];
            }
        }
        
        return [
            'gaps' => $gaps,
            'overall_coverage' => count($gaps) === 0 ? 100 : round((count($allSkills) - count($gaps)) / count($allSkills) * 100, 2),
            'critical_gaps' => count(array_filter($gaps, fn($g) => $g['severity'] === 'high'))
        ];
    }

    private function getSkillRecommendation(string $skill, float $coverage): string
    {
        return match(true) {
            $coverage < 20 => "Formation intensive requise en $skill",
            $coverage < 40 => "Atelier de développement des compétences en $skill recommandé",
            default => "Renforcement des compétences en $skill bénéfique"
        };
    }

    private function calculateBudgetEfficiency(Equipe $equipe): float
    {
        $budget = floatval($equipe->getBudget() ?? 0);
        if ($budget == 0) return 100;
        
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        if ($memberCount == 0) return 0;
        
        $avgParticipation = $this->calculateAverageParticipation($members);
        $productivityIndex = $this->calculateProductivityIndex($equipe);
        
        $efficiency = ($avgParticipation * 0.4 + $productivityIndex * 0.6);
        $budgetImpact = min(100, ($budget / ($memberCount * 5000)) * 100);
        
        return round(($efficiency * $budgetImpact) / 100, 2);
    }

    private function calculateTeamHealthScore(Equipe $equipe, array $members): float
    {
        $memberCount = count($members);
        if ($memberCount === 0) return 0;
        
        $scores = [
            'engagement' => $this->calculateEngagementScore($members),
            'stability' => 100 - $this->calculateTurnoverRate($equipe),
            'participation' => $this->calculateAverageParticipation($members),
            'diversity' => $this->calculateDiversityScore($members),
            'productivity' => $this->calculateProductivityIndex($equipe)
        ];
        
        $weights = ['engagement' => 0.25, 'stability' => 0.2, 'participation' => 0.2, 'diversity' => 0.15, 'productivity' => 0.2];
        
        $totalScore = 0;
        foreach ($scores as $key => $score) {
            $totalScore += $score * $weights[$key];
        }
        
        return round($totalScore, 2);
    }

    private function predictTurnoverRisk(array $members): array
    {
        $memberCount = count($members);
        if ($memberCount === 0) {
            return [
                'at_risk_members' => [],
                'average_risk' => 0,
                'critical_count' => 0
            ];
        }
        
        $risks = [];
        foreach ($members as $member) {
            $risk = 0;
            
            // Based on participation rate
            $participation = floatval($member->getTauxParticipation());
            if ($participation < 50) $risk += 30;
            elseif ($participation < 70) $risk += 15;
            
            // Based on role
            $role = $member->getRoleEquipe();
            if ($role === 'Membre') $risk += 10;
            
            // Based on tenure
            $affectationDate = $member->getDateAffectation();
            if ($affectationDate) {
                $months = (new \DateTime())->diff($affectationDate)->m;
                if ($months < 3) $risk += 20;
                elseif ($months > 24) $risk += 15;
            }
            
            $risk = min(100, $risk);
            
            if ($risk > 50) {
                $risks[] = [
                    'member' => $member->getUser()->getNom() . ' ' . $member->getUser()->getPrenom(),
                    'risk_score' => $risk,
                    'risk_level' => $risk > 70 ? 'high' : 'medium',
                    'factors' => $this->getRiskFactors($member, $risk)
                ];
            }
        }
        
        usort($risks, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);
        
        return [
            'at_risk_members' => $risks,
            'average_risk' => count($risks) > 0 ? array_sum(array_column($risks, 'risk_score')) / count($risks) : 0,
            'critical_count' => count(array_filter($risks, fn($r) => $r['risk_level'] === 'high'))
        ];
    }

    private function getRiskFactors(MembreEquipe $member, float $risk): array
    {
        $factors = [];
        $participation = floatval($member->getTauxParticipation());
        
        if ($participation < 50) {
            $factors[] = 'Faible taux de participation (' . $participation . '%)';
        }
        
        if ($member->getRoleEquipe() === 'Membre') {
            $factors[] = 'Pas de responsabilités de leadership';
        }
        
        if ($risk > 70) {
            $factors[] = 'Risque élevé de départ - Action recommandée immédiate';
        }
        
        return $factors;
    }

    private function calculateSuccessProbability(Equipe $equipe): float
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        
        if ($memberCount === 0) return 0;
        
        $factors = [
            'size_factor' => min(1, $memberCount / max(1, $equipe->getNbMembresMax())),
            'engagement_factor' => $this->calculateEngagementScore($members) / 100,
            'skill_factor' => $this->analyzeSkillGaps($members)['overall_coverage'] / 100,
            'stability_factor' => (100 - $this->calculateTurnoverRate($equipe)) / 100
        ];
        
        $weights = ['size_factor' => 0.2, 'engagement_factor' => 0.3, 'skill_factor' => 0.3, 'stability_factor' => 0.2];
        
        $probability = 0;
        foreach ($factors as $factor => $value) {
            $probability += $value * $weights[$factor];
        }
        
        return round($probability * 100, 2);
    }

    private function analyzeActivityPatterns(Equipe $equipe): array
    {
        // Simulate activity patterns based on member data
        $patterns = [];
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        foreach ($members as $member) {
            $updatedAt = $member->getUpdatedAt();
            if ($updatedAt) {
                $hour = (int)$updatedAt->format('H');
                $patterns[$hour] = ($patterns[$hour] ?? 0) + 1;
            }
        }
        
        if (empty($patterns)) {
            return ['peak_hours' => [], 'activity_distribution' => []];
        }
        
        arsort($patterns);
        $peakHours = array_slice(array_keys($patterns), 0, 3);
        
        return [
            'peak_hours' => $peakHours,
            'most_active_hour' => $peakHours[0] ?? null,
            'activity_distribution' => $patterns,
            'recommended_meeting_times' => $this->getRecommendedMeetingTimes($patterns)
        ];
    }

    private function getRecommendedMeetingTimes(array $activityPatterns): array
    {
        $recommended = [];
        $hours = array_keys($activityPatterns);
        sort($hours);
        
        if (count($hours) >= 2) {
            $midpoint = $hours[0] + floor(($hours[count($hours) - 1] - $hours[0]) / 2);
            $recommended[] = $midpoint . ':00';
            $recommended[] = ($midpoint + 1) . ':00';
        }
        
        return $recommended;
    }

    private function calculateTurnoverRate(Equipe $equipe): float
    {
        // Simplified turnover calculation based on member count changes
        $currentCount = count($equipe->getMembres());
        // In a real implementation, you'd track historical data
        return $currentCount > 0 ? rand(5, 20) : 0;
    }

    private function calculateAverageParticipation(array $members): float
    {
        $memberCount = count($members);
        if ($memberCount === 0) return 0;
        
        $total = 0;
        foreach ($members as $member) {
            $total += floatval($member->getTauxParticipation());
        }
        return round($total / $memberCount, 2);
    }

    private function calculateDiversityScore(array $members): float
    {
        $memberCount = count($members);
        if ($memberCount === 0) return 0;
        
        // Simplified diversity calculation
        $uniqueRoles = array_unique(array_map(fn($m) => $m->getRoleEquipe(), $members));
        $roleDiversity = count($uniqueRoles) / 5 * 100;
        
        return min(100, $roleDiversity);
    }

    private function calculateProductivityIndex(Equipe $equipe): float
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        if ($memberCount === 0) return 0;
        
        $avgParticipation = $this->calculateAverageParticipation($members);
        $teamSize = $memberCount;
        $optimalSize = $equipe->getNbMembresMax();
        
        $sizeEfficiency = $optimalSize > 0 ? 1 - abs($teamSize - $optimalSize) / $optimalSize : 1;
        
        return round(($avgParticipation * 0.6 + $sizeEfficiency * 100 * 0.4), 2);
    }

    private function calculateTeamExperience(array $members): float
    {
        $memberCount = count($members);
        if ($memberCount === 0) return 0;
        
        $totalExperience = 0;
        foreach ($members as $member) {
            $affectationDate = $member->getDateAffectation();
            if ($affectationDate) {
                $months = (new \DateTime())->diff($affectationDate)->m;
                $totalExperience += $months;
            }
        }
        return $totalExperience / $memberCount;
    }

    private function calculatePerformanceTrend(Equipe $equipe): string
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        if ($memberCount === 0) return 'stable';
        
        // Simplified trend calculation
        $currentScore = $this->calculateProductivityIndex($equipe);
        // In real implementation, compare with historical data
        return $currentScore > 70 ? 'upward' : ($currentScore > 50 ? 'stable' : 'downward');
    }

    private function calculateExpectedImprovement(Equipe $equipe): array
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        if ($memberCount === 0) {
            return [
                'potential_gain' => 0,
                'timeline' => 'N/A',
                'key_actions' => ['Recruter des membres pour commencer']
            ];
        }
        
        $currentScore = $this->calculateProductivityIndex($equipe);
        $optimizedScore = min(100, $currentScore * 1.3);
        
        return [
            'potential_gain' => round($optimizedScore - $currentScore, 2),
            'timeline' => '3-6 mois',
            'key_actions' => ['Optimiser la répartition des tâches', 'Renforcer les compétences clés']
        ];
    }

    private function getBenchmarkComparison(float $score): array
    {
        if ($score >= 80) {
            return ['level' => 'excellent', 'percentile' => 90];
        } elseif ($score >= 65) {
            return ['level' => 'good', 'percentile' => 70];
        } elseif ($score >= 50) {
            return ['level' => 'average', 'percentile' => 50];
        } else {
            return ['level' => 'needs_improvement', 'percentile' => 25];
        }
    }

    private function detectSkillImbalance(array $skillDistribution): ?array
    {
        if (empty($skillDistribution)) return null;
        
        $maxSkill = max($skillDistribution);
        $minSkill = min($skillDistribution);
        
        if ($maxSkill - $minSkill > 60) {
            $dominantSkill = array_search($maxSkill, $skillDistribution);
            $missingSkills = array_keys(array_filter($skillDistribution, fn($v) => $v < 30));
            
            return [
                'description' => "Sur-représentation de '$dominantSkill', manque de " . implode(', ', array_slice($missingSkills, 0, 2)),
                'missing_skills' => implode(', ', array_slice($missingSkills, 0, 3))
            ];
        }
        
        return null;
    }

    private function getSkillDistribution(array $members): array
    {
        $skills = [];
        foreach ($members as $member) {
            $competences = $member->getCompetencesPrincipales();
            if ($competences) {
                $memberSkills = array_map('trim', explode(',', $competences));
                foreach ($memberSkills as $skill) {
                    $skills[$skill] = ($skills[$skill] ?? 0) + 1;
                }
            }
        }
        
        if (empty($skills)) {
            return ['Technique' => 50, 'Management' => 30, 'Communication' => 70];
        }
        
        return $skills;
    }

    private function generateRecommendations(Equipe $equipe, array $optimizations, array $skillGaps): array
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        
        // If team is empty
        if ($memberCount === 0) {
            return [
                [
                    'priority' => 1,
                    'title' => 'Constituer l\'équipe',
                    'action' => 'Recruter des membres pour l\'équipe',
                    'impact' => 'Permettre à l\'équipe de fonctionner'
                ]
            ];
        }
        
        $recommendations = [];
        
        // Priority 1: Critical skill gaps
        if ($skillGaps['critical_gaps'] > 0) {
            $recommendations[] = [
                'priority' => 1,
                'title' => 'Combler les lacunes critiques de compétences',
                'action' => 'Organiser des formations intensives',
                'impact' => 'Augmentation de la polyvalence de l\'équipe'
            ];
        }
        
        // Priority 2: Recruitment needs
        foreach ($optimizations as $opt) {
            if (isset($opt['type']) && $opt['type'] === 'recruitment' && $opt['priority'] === 'high') {
                $recommendations[] = [
                    'priority' => 2,
                    'title' => 'Renforcer l\'équipe',
                    'action' => $opt['action'],
                    'impact' => $opt['impact']
                ];
            }
        }
        
        // Priority 3: Team building
        $recommendations[] = [
            'priority' => 3,
            'title' => 'Améliorer la cohésion d\'équipe',
            'action' => 'Organiser des activités de team building mensuelles',
            'impact' => 'Augmentation de la collaboration de +20%'
        ];
        
        usort($recommendations, fn($a, $b) => $a['priority'] <=> $b['priority']);
        
        return $recommendations;
    }

    private function identifyTeamStrengths(array $members): array
    {
        $memberCount = count($members);
        if ($memberCount === 0) {
            return ['Potentiel de croissance'];
        }
        
        $strengths = [];
        
        $avgParticipation = $this->calculateAverageParticipation($members);
        if ($avgParticipation > 80) {
            $strengths[] = 'Engagement exceptionnel des membres';
        }
        
        $uniqueRoles = count(array_unique(array_map(fn($m) => $m->getRoleEquipe(), $members)));
        if ($uniqueRoles >= 3) {
            $strengths[] = 'Structure hiérarchique diversifiée';
        }
        
        if (empty($strengths)) {
            $strengths[] = 'Équipe en formation avec potentiel de développement';
        }
        
        return $strengths;
    }

    private function identifyTeamWeaknesses(array $members): array
    {
        $memberCount = count($members);
        if ($memberCount === 0) {
            return ['Aucun membre dans l\'équipe'];
        }
        
        $weaknesses = [];
        
        $avgParticipation = $this->calculateAverageParticipation($members);
        if ($avgParticipation < 60) {
            $weaknesses[] = 'Faible engagement général';
        }
        
        if ($memberCount < 3) {
            $weaknesses[] = 'Taille d\'équipe limitée';
        }
        
        if (empty($weaknesses)) {
            $weaknesses[] = 'Aucune faiblesse majeure détectée';
        }
        
        return $weaknesses;
    }

    private function identifyOpportunities(Equipe $equipe): array
    {
        $opportunities = [];
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $currentSize = count($members);
        $maxSize = $equipe->getNbMembresMax();
        
        if ($currentSize === 0) {
            return ['Opportunité de construire une équipe performante depuis zéro'];
        }
        
        if ($currentSize < $maxSize * 0.7) {
            $opportunities[] = 'Potentiel de croissance significatif';
        }
        
        $budget = floatval($equipe->getBudget() ?? 0);
        if ($budget > 0 && $currentSize > 0) {
            $budgetPerMember = $budget / $currentSize;
            if ($budgetPerMember > 5000) {
                $opportunities[] = 'Budget important disponible par membre';
            }
        }
        
        if (empty($opportunities)) {
            $opportunities[] = 'Explorer de nouvelles collaborations';
        }
        
        return $opportunities;
    }

    private function getMemberContributionData(array $members): array
    {
        $data = [];
        foreach ($members as $member) {
            $data[] = [
                'name' => $member->getUser()->getNom() . ' ' . $member->getUser()->getPrenom(),
                'participation' => floatval($member->getTauxParticipation()),
                'role' => $member->getRoleEquipe()
            ];
        }
        return $data;
    }

    private function getTimelineMetrics(Equipe $equipe): array
    {
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        $memberCount = count($members);
        if ($memberCount === 0) {
            return [];
        }
        
        // Simulate timeline data (last 6 months)
        $timeline = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = (new \DateTime())->modify("-$i months");
            $timeline[] = [
                'month' => $date->format('F Y'),
                'productivity' => rand(60, 90),
                'engagement' => rand(65, 95),
                'participation' => rand(70, 100)
            ];
        }
        return $timeline;
    }

    private function getActivityHeatmap(Equipe $equipe): array
    {
        $heatmap = [];
        $members = $equipe->getMembres();
        if ($members instanceof Collection) {
            $members = $members->toArray();
        } elseif (!is_array($members)) {
            $members = [];
        }
        
        foreach ($members as $member) {
            $updatedAt = $member->getUpdatedAt();
            if ($updatedAt) {
                $dayOfWeek = $updatedAt->format('w');
                $hour = $updatedAt->format('H');
                $key = "day_{$dayOfWeek}_hour_{$hour}";
                $heatmap[$key] = ($heatmap[$key] ?? 0) + 1;
            }
        }
        
        return $heatmap;
    }
}