<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Mail\PortfolioInquiryReceived;
use App\Models\Inquiry;
use App\Models\PortfolioSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display the public contact page.
     */
    public function create(): View
    {
        return view('portfolio.contact', [
            'settings' => PortfolioSetting::query()->first() ?? new PortfolioSetting(PortfolioSetting::defaults()),
        ]);
    }

    /**
     * Store a new portfolio inquiry.
     */
    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $inquiry = Inquiry::query()->create([
            ...$request->validated(),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $recipientAddress = config('portfolio.contact_recipient_address');

        if (filled($recipientAddress)) {
            Mail::to($recipientAddress)->queue(
                (new PortfolioInquiryReceived($inquiry))->afterCommit(),
            );
        }

        return to_route('contact.create')
            ->with('status', 'Thanks for reaching out. Your message is in the queue.');
    }
}
