<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Widget;

use Livewire\Component;
use Modules\SmartBot\App\Services\BotEngineService;
use Modules\SmartBot\App\Services\EntityResolverService;
use Modules\SmartBot\App\Models\BotSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class ChatWidget extends Component
{
    public string $uuid = '';
    public string $userMessage = '';
    public array $messages = [];
    public array $suggestions = [];
    public string $botName = 'SmartBot';
    public string $primaryColor = '#6366f1';
    public bool $isWidgetOpen = false;
    public bool $isThinking = false;
    public bool $isStandalone = false;
    public string $lastUserMessage = '';
    public bool $allowCustomTyping = true;
    public int $cartItemCount = 0;

    protected $listeners = [
        'cartUpdated' => 'updateCartCount'
    ];

    public function updateCartCount()
    {
        $cart = session()->get('market_cart', []);
        $this->cartItemCount = (int) array_sum(array_column($cart, 'quantity'));
    }

    public function mount(?string $sessionUuid = null)
    {
        $this->botName = (string) BotSetting::getValue('name', 'SmartBot');
        $this->primaryColor = (string) BotSetting::getValue('primary_color', '#6366f1');
        $this->allowCustomTyping = filter_var(BotSetting::getValue('allow_custom_typing', true), FILTER_VALIDATE_BOOLEAN);
        $this->updateCartCount();
        
        $this->uuid = $sessionUuid ?? (string) Session::get('smartbot_session_uuid');
        if (!$this->uuid) {
            $this->uuid = Str::uuid()->toString();
            Session::put('smartbot_session_uuid', $this->uuid);
        }

        if ($this->isStandalone) {
            $this->isWidgetOpen = true;
        }

        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid, request()->fullUrl(), [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->loadMessages($session);
        $this->suggestions = $engine->getSuggestedQuestions((int) BotSetting::getValue('max_suggestions', 5));
    }

    private function loadMessages($session): void
    {
        $this->messages = [];

        // Add welcome message if conversation is empty
        if ($session->messages()->count() === 0) {
            $this->messages[] = [
                'role' => 'bot',
                'content' => (string) BotSetting::getValue('welcome_message', 'سلام! چطور می‌توانم کمکتان کنم؟'),
                'answer_type' => 'text',
                'products' => [],
                'created_at' => now()->toIso8601String(),
            ];
        } else {
            $resolver = app(EntityResolverService::class);
            foreach ($session->messages()->orderBy('id', 'asc')->get() as $msg) {
                $products = [];
                if ($msg->answer && $msg->answer->answer_type === 'product_list' && !empty($msg->answer->entity_ids)) {
                    $products = $resolver->resolveProducts($msg->answer->entity_ids);
                }

                $this->messages[] = [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'answer_type' => $msg->answer ? $msg->answer->answer_type : 'text',
                    'products' => $products,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            }
        }
    }

    public function sendMessage(?string $overrideText = null): void
    {
        $text = trim($overrideText ?? $this->userMessage);
        if (!$text) return;

        // Record last user message for deferred process
        $this->lastUserMessage = $text;

        // Add user message to state instantly
        $this->messages[] = [
            'role' => 'user',
            'content' => $text,
            'answer_type' => 'text',
            'products' => [],
            'created_at' => now()->toIso8601String(),
        ];

        $this->userMessage = '';
        $this->isThinking = true;
    }

    // Handles processing message after user sees thinking state
    public function processMessage(): void
    {
        $text = $this->lastUserMessage;
        if (!$text) {
            $this->isThinking = false;
            return;
        }

        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid);

        $botReply = $engine->sendMessage($session, $text);

        $this->messages[] = $botReply;
        
        // Reset properties
        $this->lastUserMessage = '';
        $this->isThinking = false;

        $this->dispatch('chatScrollToBottom');
    }

    public function addToCart(int $productId): void
    {
        $resolver = app(EntityResolverService::class);
        $params = $resolver->getAddToCartParams($productId);

        if ($params) {
            // Dispatch event to Market's CartManager
            $this->dispatch('addToCart', variantId: $params['variant_id'], vendorProductId: $params['vendor_product_id'], quantity: 1);
            
            // Mark session as converted in metadata
            $engine = app(BotEngineService::class);
            $session = $engine->getOrCreateSession($this->uuid);
            $meta = $session->metadata ?? [];
            $meta['added_to_cart'] = true;
            $session->update(['metadata' => $meta]);

            $this->dispatch('notify', type: 'success', text: 'محصول با موفقیت به سبد خرید اضافه شد.');
        } else {
            $this->dispatch('notify', type: 'error', text: 'این محصول در حال حاضر غیرفعال یا ناموجود است.');
        }
    }

    public function toggleWidget(): void
    {
        $this->isWidgetOpen = !$this->isWidgetOpen;
        if ($this->isWidgetOpen) {
            $this->dispatch('chatScrollToBottom');
        }
    }

    public function resetSession(): void
    {
        $this->uuid = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\Session::put('smartbot_session_uuid', $this->uuid);
        
        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid, request()->fullUrl(), [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->loadMessages($session);
        $this->userMessage = '';
        $this->isThinking = false;
        
        $this->dispatch('chatScrollToBottom');
    }

    public function render()
    {
        return view('smartbot::livewire.widget.chat-widget');
    }
}
