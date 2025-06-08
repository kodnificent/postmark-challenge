<?php

namespace App\Services\Review;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAi implements Reviewer
{
    public const BASE_URL = 'https://api.openai.com/v1';

    public function __construct(
        protected readonly array $config
    ) {}

    protected function getClient(): PendingRequest
    {
        return Http::withToken($this->config['api_key'])
            ->baseUrl(self::BASE_URL);
    }

    protected function createAssistant(): string
    {
        $response = $this->getClient()
            ->post('/assistants', [
                'model' => 'gpt-4-turbo',
                'name' => 'Contract Reviewer',
                'instructions' => <<<TEXT
                    You are a legal assistant that reviews legal contracts. A user has uploaded a contract in PDF format.

                    Please read the content and return your analysis.

                    Make sure your response is valid JSON. If the contract seems incomplete or unreadable or is not a contract, say so in the summary and leave the clause list empty.
                    You must only analyze contracts.
                TEXT,
                'tools' => [['type' => 'retrieval']],
            ])
            ->throw();

        return $response->json('id');
    }

    protected function createThread(string $file_id): string
    {
        $response = $this->getClient()
            ->post('/threads', [
                'messages' => [
                    'role' => 'user',
                    'content' => 'Please analyze this contract. I want a plain summary, key clauses with comments and risk scores, and an overall risk score.',
                    'file_ids' => [$file_id],
                ],
            ])
            ->throw();

        return $response->json('id');
    }

    protected function runAssistant(string $assistant_id, string $thread_id): string
    {
        $response = $this->getClient()
            ->post("/threads/{$thread_id}/runs", [
                'assistant_id' => $assistant_id,
            ])
            ->throw();

        return $response->json('id');
    }

    public function analyze(string $content): Output
    {
        $response = $this->getClient()
            ->post('/responses', [
                'model' => 'gpt-4.1-2025-04-14',
                'input' => [
                    [
                        'role' => 'system',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'You are a legal assistant that reviews and summarizes contracts for users. You goal is to outline key details of the contract, break down jargons and assign risk score base on your legal knowledge. Output must be strictly json format. If the contract seems incomplete or unreadable or is not a contract, say so in the summary and leave the clause list empty and set the risk score to 0. You must analyze ONLY contracts.'
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => 'Review this contract',
                            ],
                            [
                                'type' => 'input_text',
                                'text' => $content,
                            ],
                        ],
                    ]
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'contract_analysis',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => [
                                    'type' => 'string',
                                    'description' => 'Title of the contract. Not more than 100 Characters',
                                ],
                                'summary' => [
                                    'type' => 'string',
                                    'description' => 'Very Short plain-English summary here'
                                ],
                                'risk_score' => [
                                    'type' => 'number',
                                    'description' => 'Risk score from 0 to 100, where 0 is safe and 100 is not safe'
                                ],
                                'risk_score_comment' => [
                                    'type' => 'string',
                                    'description' => 'Short comment about the risk score'
                                ],
                                'clauses' => [
                                    'type' => 'array',
                                    'description' => 'List of clauses in the contract with their risk details',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => [
                                                'type' => 'string',
                                                'description' => 'Clause Title. Not more than 100 characters'
                                            ],
                                            'comment' => [
                                                'type' => 'string',
                                                'description' => 'Explanation of what the clause says, based on the actual contract'
                                            ],
                                            'risk_score' => [
                                                'type' => 'number',
                                                'description' => 'Risk score associated with the clause, from 0 to 100'
                                            ],
                                            'risk_score_comment' => [
                                                'type' => 'string',
                                                'description' => 'Short comment about the risk score'
                                            ]
                                        ],
                                        'required' => [
                                            'title',
                                            'comment',
                                            'risk_score',
                                            'risk_score_comment'
                                        ],
                                        'additionalProperties' => false
                                    ]
                                ],
                            ],
                            'required' => [
                                'title',
                                'summary',
                                'risk_score',
                                'clauses',
                                'risk_score_comment'
                            ],
                            'additionalProperties' => false
                        ]
                    ]
                    ],
                'temperature' => 1,
                'max_output_tokens' => 2048,
                'top_p' => 1
            ])
            ->throw()
            ->json('output.0.content.0.text');

        return new Output(json_decode($response, true));
    }
}
