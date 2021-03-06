<?php

require_once 'AppController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserDto.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/ConversationRepository.php';
require_once __DIR__ . '/../repository/RankRepository.php';
require_once __DIR__ . '/../repository/RatingRepository.php';

class ConversationController extends AppController
{

    private UserRepository $userRepository;
    private ConversationRepository $conversationRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
        $this->conversationRepository = new ConversationRepository();
    }

    public function message()
    {
        $decoded = $this->decodeJsonRequest();
        if(!$decoded) {
            return null;
        }
        $message = $decoded['message'];
        $conversationId = $decoded['conversationId'];
        $this->conversationRepository->newMessage($conversationId, RouteGuard::getAuthenticatedUserId(), $message);

        echo json_encode($this->conversationRepository->getConversationMessagesAssoc($conversationId));
    }

    public function conversation()
    {
        $otherUserId = $_POST['userId'];
        $currentUserId = RouteGuard::getAuthenticatedUserId();
        $conversations = $this->conversationRepository->getUserConversations($currentUserId) ?: [];
        $selectedConversation = null;
        $messages = [];

        if (isset($otherUserId)) {
            $selectedConversation = $this->setUpConversation($currentUserId, $otherUserId);
            $conversations = $this->conversationRepository->getUserConversations($currentUserId) ?: [];
            $messages = $this->conversationRepository->getConversationMessages($selectedConversation->getId());
        }
        else if (count($conversations) > 0) {
            $selectedConversation = $conversations[0];
            $messages = $this->conversationRepository->getConversationMessages($selectedConversation->getId());
        }

        try {
            return $this->render('conversation', [
                'user' => $this->userRepository->getUserDtoById($currentUserId),
                'conversations' => $conversations,
                'selected' => $selectedConversation,
                'messages' => $messages
            ]);
        } catch (UnexpectedValueException $e){
            return $this->handleException($e);
        }
    }

    private function setUpConversation($currentUserId, $otherUserId) {
        try {
            $conversationId = $this->conversationRepository->getOrCreateConversation($currentUserId, $otherUserId);
        } catch (UnexpectedValueException $e) {
            return $this->handleException($e);
        }

        $conversations = $this->conversationRepository->getUserConversations($currentUserId);
        $selectedConversation = array_filter($conversations, function ($conv) use ($conversationId) {
            return $conv->getId() === $conversationId;
        });

        return reset($selectedConversation);
    }
}