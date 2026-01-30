<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OpportunityController extends Controller
{
    /**
     * Show the home page with search
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Search for opportunities
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        // Get demo opportunities
        $opportunities = $this->getDemoOpportunities();

        // Simple filter based on query
        if ($query) {
            $opportunities = array_filter($opportunities, function ($opportunity) use ($query) {
                return stripos($opportunity['objective'], $query) !== false ||
                       stripos($opportunity['tagline'], $query) !== false ||
                       $this->skillsContain($opportunity['skills'], $query);
            });
        }

        return view('results', [
            'opportunities' => $opportunities,
            'query' => $query,
        ]);
    }

    /**
     * Apply to an opportunity
     */
    public function apply($id)
    {
        $user = Session::get('user');

        return redirect()
            ->route('opportunities.search')
            ->with('success', "Application submitted successfully for opportunity #{$id}!");
    }

    /**
     * Check if skills array contains the query
     */
    private function skillsContain($skills, $query)
    {
        foreach ($skills as $skill) {
            if (stripos($skill['name'], $query) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get demo opportunities data
     */
    private function getDemoOpportunities()
    {
        return [
            [
                "id" => "NwqBEBkd",
                "objective" => "Software Development Contributor (in exchange for equity - no cash)",
                "slug" => "torreai-software-development-contributor-in-exchange-for-equity-no-cash-4",
                "tagline" => "You'll shape the future of global recruitment by investing your expertise to build impactful AI products.",
                "theme" => "lime500",
                "type" => "flexible-jobs",
                "opportunity" => "flexible-job",
                "organizations" => [
                    [
                        "id" => 748404,
                        "hashedId" => "0Zy6z36o",
                        "name" => "Torre.ai",
                        "status" => "approved",
                        "size" => 160,
                        "publicId" => "Torre",
                        "picture" => "https://res.cloudinary.com/torre-technologies-co/image/upload/v1740263043/origin/bio/crawled-organizations/Torre.ai1740263041616_erh4zm_pkeixo.jpg",
                        "theme" => "lime500"
                    ]
                ],
                "locations" => [],
                "timezones" => null,
                "remote" => true,
                "external" => false,
                "deadline" => null,
                "created" => "2025-12-16T15:11:54.000Z",
                "status" => "open",
                "commitment" => "part-time",
                "compensation" => [
                    "data" => [
                        "code" => "fixed",
                        "currency" => "USD",
                        "minAmount" => 0.0,
                        "minHourlyUSD" => 0.0,
                        "maxAmount" => null,
                        "maxHourlyUSD" => 0.0,
                        "periodicity" => "hourly",
                        "negotiable" => false,
                        "conversionRateUSD" => 1.0
                    ],
                    "visible" => true
                ],
                "skills" => [
                    [
                        "name" => "Software development",
                        "experience" => "potential-to-develop",
                        "proficiency" => "proficient"
                    ],
                    [
                        "name" => "PHP",
                        "experience" => "2-years-experience",
                        "proficiency" => "proficient"
                    ],
                    [
                        "name" => "Laravel",
                        "experience" => "1-year-experience",
                        "proficiency" => "competent"
                    ]
                ],
                "place" => [
                    "remote" => true,
                    "anywhere" => true,
                    "timezone" => false,
                    "locationType" => "remote_anywhere",
                    "location" => []
                ],
                "additionalCompensation" => ["stocks"],
                "additionalCompensationDetails" => [
                    "stocks" => "1"
                ],
            ],
            [
                "id" => "XyZ123AB",
                "objective" => "Senior Full Stack Developer",
                "slug" => "acme-corp-senior-full-stack-developer",
                "tagline" => "Build scalable web applications with cutting-edge technologies in a collaborative environment.",
                "theme" => "blue500",
                "type" => "permanent",
                "opportunity" => "job",
                "organizations" => [
                    [
                        "id" => 123456,
                        "hashedId" => "ABC123",
                        "name" => "Acme Corp",
                        "status" => "approved",
                        "size" => 500,
                        "publicId" => "AcmeCorp",
                        "picture" => "https://via.placeholder.com/150",
                        "theme" => "blue500"
                    ]
                ],
                "locations" => ["San Francisco, CA", "Remote"],
                "timezones" => ["PST", "EST"],
                "remote" => true,
                "external" => false,
                "deadline" => "2026-03-31T23:59:59.000Z",
                "created" => "2026-01-15T10:00:00.000Z",
                "status" => "open",
                "commitment" => "full-time",
                "compensation" => [
                    "data" => [
                        "code" => "range",
                        "currency" => "USD",
                        "minAmount" => 120000.0,
                        "minHourlyUSD" => 57.69,
                        "maxAmount" => 160000.0,
                        "maxHourlyUSD" => 76.92,
                        "periodicity" => "yearly",
                        "negotiable" => true,
                        "conversionRateUSD" => 1.0
                    ],
                    "visible" => true
                ],
                "skills" => [
                    [
                        "name" => "JavaScript",
                        "experience" => "5-years-experience",
                        "proficiency" => "expert"
                    ],
                    [
                        "name" => "React",
                        "experience" => "3-years-experience",
                        "proficiency" => "proficient"
                    ],
                    [
                        "name" => "Node.js",
                        "experience" => "3-years-experience",
                        "proficiency" => "proficient"
                    ],
                    [
                        "name" => "PostgreSQL",
                        "experience" => "2-years-experience",
                        "proficiency" => "competent"
                    ]
                ],
                "place" => [
                    "remote" => true,
                    "anywhere" => false,
                    "timezone" => true,
                    "locationType" => "remote_timezone",
                    "location" => ["USA"]
                ],
                "additionalCompensation" => ["health-insurance", "401k"],
                "additionalCompensationDetails" => [
                    "health-insurance" => "Full coverage",
                    "401k" => "4% match"
                ],
            ],
            [
                "id" => "DEF456GH",
                "objective" => "DevOps Engineer",
                "slug" => "techstart-devops-engineer",
                "tagline" => "Automate infrastructure and streamline deployment processes for high-traffic applications.",
                "theme" => "purple500",
                "type" => "contract",
                "opportunity" => "contract",
                "organizations" => [
                    [
                        "id" => 789012,
                        "hashedId" => "XYZ789",
                        "name" => "TechStart Inc",
                        "status" => "approved",
                        "size" => 75,
                        "publicId" => "TechStart",
                        "picture" => "https://via.placeholder.com/150",
                        "theme" => "purple500"
                    ]
                ],
                "locations" => ["Remote"],
                "timezones" => null,
                "remote" => true,
                "external" => false,
                "deadline" => "2026-02-28T23:59:59.000Z",
                "created" => "2026-01-20T14:30:00.000Z",
                "status" => "open",
                "commitment" => "full-time",
                "compensation" => [
                    "data" => [
                        "code" => "hourly",
                        "currency" => "USD",
                        "minAmount" => 75.0,
                        "minHourlyUSD" => 75.0,
                        "maxAmount" => 95.0,
                        "maxHourlyUSD" => 95.0,
                        "periodicity" => "hourly",
                        "negotiable" => true,
                        "conversionRateUSD" => 1.0
                    ],
                    "visible" => true
                ],
                "skills" => [
                    [
                        "name" => "Docker",
                        "experience" => "3-years-experience",
                        "proficiency" => "proficient"
                    ],
                    [
                        "name" => "Kubernetes",
                        "experience" => "2-years-experience",
                        "proficiency" => "competent"
                    ],
                    [
                        "name" => "AWS",
                        "experience" => "4-years-experience",
                        "proficiency" => "expert"
                    ],
                    [
                        "name" => "Terraform",
                        "experience" => "2-years-experience",
                        "proficiency" => "proficient"
                    ]
                ],
                "place" => [
                    "remote" => true,
                    "anywhere" => true,
                    "timezone" => false,
                    "locationType" => "remote_anywhere",
                    "location" => []
                ],
                "additionalCompensation" => [],
                "additionalCompensationDetails" => [],
            ],
            [
                "id" => "JKL789MN",
                "objective" => "Frontend Developer - React Specialist",
                "slug" => "webagency-frontend-react-specialist",
                "tagline" => "Create beautiful, responsive user interfaces for modern web applications.",
                "theme" => "pink500",
                "type" => "permanent",
                "opportunity" => "job",
                "organizations" => [
                    [
                        "id" => 345678,
                        "hashedId" => "WEB456",
                        "name" => "WebAgency Pro",
                        "status" => "approved",
                        "size" => 30,
                        "publicId" => "WebAgency",
                        "picture" => "https://via.placeholder.com/150",
                        "theme" => "pink500"
                    ]
                ],
                "locations" => ["New York, NY", "Remote"],
                "timezones" => ["EST"],
                "remote" => true,
                "external" => false,
                "deadline" => null,
                "created" => "2026-01-25T09:00:00.000Z",
                "status" => "open",
                "commitment" => "full-time",
                "compensation" => [
                    "data" => [
                        "code" => "range",
                        "currency" => "USD",
                        "minAmount" => 90000.0,
                        "minHourlyUSD" => 43.27,
                        "maxAmount" => 120000.0,
                        "maxHourlyUSD" => 57.69,
                        "periodicity" => "yearly",
                        "negotiable" => false,
                        "conversionRateUSD" => 1.0
                    ],
                    "visible" => true
                ],
                "skills" => [
                    [
                        "name" => "React",
                        "experience" => "4-years-experience",
                        "proficiency" => "expert"
                    ],
                    [
                        "name" => "TypeScript",
                        "experience" => "3-years-experience",
                        "proficiency" => "proficient"
                    ],
                    [
                        "name" => "CSS",
                        "experience" => "5-years-experience",
                        "proficiency" => "expert"
                    ],
                    [
                        "name" => "Tailwind CSS",
                        "experience" => "2-years-experience",
                        "proficiency" => "proficient"
                    ]
                ],
                "place" => [
                    "remote" => true,
                    "anywhere" => false,
                    "timezone" => true,
                    "locationType" => "remote_timezone",
                    "location" => ["USA"]
                ],
                "additionalCompensation" => ["health-insurance", "pto"],
                "additionalCompensationDetails" => [
                    "health-insurance" => "Full family coverage",
                    "pto" => "Unlimited PTO"
                ],
            ]
        ];
    }
}
