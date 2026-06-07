<?php

class Storage {
    private $storageDir;
    
    public function __construct($storageDir) {
        $this->storageDir = $storageDir;
    }
    
    private function getFilePath($type) {
        return $this->storageDir . '/' . $type . '.json';
    }
    
    private function readFile($type) {
        $filePath = $this->getFilePath($type);
        if (!file_exists($filePath)) {
            return [];
        }
        $content = file_get_contents($filePath);
        return json_decode($content, true) ?: [];
    }
    
    private function writeFile($type, $data) {
        $filePath = $this->getFilePath($type);
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    // Sessions
    public function getSessions() {
        return $this->readFile('sessions');
    }
    
    public function getSession($id) {
        $sessions = $this->getSessions();
        foreach ($sessions as $session) {
            if ($session['id'] === $id) {
                return $session;
            }
        }
        return null;
    }
    
    public function createSession($data) {
        $sessions = $this->getSessions();
        $newSession = [
            'id' => generateId(),
            'name' => $data['name'],
            'participants' => isset($data['participants']) ? $data['participants'] : [],
            'currentGame' => null,
            'stats' => [
                'totalPresentations' => 0,
                'averageTime' => 0,
                'participantCount' => 0
            ],
            'createdAt' => date('Y-m-d H:i:s')
        ];
        $sessions[] = $newSession;
        $this->writeFile('sessions', $sessions);
        return $newSession;
    }
    
    public function updateSession($id, $data) {
        $sessions = $this->getSessions();
        foreach ($sessions as &$session) {
            if ($session['id'] === $id) {
                $session = array_merge($session, $data);
                $this->writeFile('sessions', $sessions);
                return $session;
            }
        }
        return null;
    }
    
    public function deleteSession($id) {
        $sessions = $this->getSessions();
        $sessions = array_filter($sessions, function($session) use ($id) {
            return $session['id'] !== $id;
        });
        $this->writeFile('sessions', array_values($sessions));
        return true;
    }
    
    // Game Configs
    public function getGameConfigs() {
        $configs = $this->readFile('game_configs');
        if (empty($configs)) {
            // Configurações padrão
            $configs = [
                'speedIntro' => [
                    'items' => [
                        'Sua maior conquista profissional',
                        'Um hobby que você adora',
                        'Seu filme favorito e por quê',
                        'Uma habilidade que poucos conhecem',
                        'Seu destino de viagem dos sonhos',
                        'Uma comida que você não vive sem',
                        'Seu animal de estimação ou favorito',
                        'Uma pessoa que te inspira'
                    ]
                ],
                'emojiIntro' => [
                    'emojis' => ['😀', '🎉', '🚀', '💡', '🎯', '🌟', '🔥', '💪', '🎨', '🎵', '📚', '☕', '🌈', '⚡', '🎭', '🏆', '🌍', '💼', '🎮', '🍕']
                ],
                'storyBuilder' => [
                    'starters' => [
                        'Era uma vez uma equipe incrível que...',
                        'Em um dia normal de trabalho, algo extraordinário aconteceu...',
                        'Ninguém esperava que aquela reunião...',
                        'A jornada começou quando...',
                        'Dizem que o impossível não existe, mas...'
                    ]
                ],
                'mysteryBox' => [
                    'questions' => [
                        'Se você pudesse ter um superpoder no trabalho, qual seria?',
                        'Qual foi o momento mais engraçado que você viveu em uma reunião?',
                        'Se sua vida fosse um filme, qual seria o título?',
                        'Qual conselho você daria para você de 5 anos atrás?',
                        'Se você pudesse jantar com qualquer pessoa, viva ou morta, quem seria?',
                        'Qual é a coisa mais estranha no seu histórico de busca?',
                        'Se você ganhasse na loteria amanhã, o que faria?',
                        'Qual habilidade você gostaria de aprender da noite para o dia?'
                    ]
                ]
            ];
            $this->writeFile('game_configs', $configs);
        }
        return $configs;
    }
    
    public function updateGameConfig($gameType, $config) {
        $configs = $this->getGameConfigs();
        $configs[$gameType] = $config;
        $this->writeFile('game_configs', $configs);
        return $configs[$gameType];
    }
    
    // Presentations
    public function createPresentation($data) {
        $presentations = $this->readFile('presentations');
        $newPresentation = [
            'id' => generateId(),
            'sessionId' => $data['sessionId'],
            'participant' => $data['participant'],
            'gameType' => $data['gameType'],
            'duration' => isset($data['duration']) ? $data['duration'] : 0,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        $presentations[] = $newPresentation;
        $this->writeFile('presentations', $presentations);
        return $newPresentation;
    }
    
    public function getPresentationsBySession($sessionId) {
        $presentations = $this->readFile('presentations');
        return array_filter($presentations, function($p) use ($sessionId) {
            return $p['sessionId'] === $sessionId;
        });
    }
}
