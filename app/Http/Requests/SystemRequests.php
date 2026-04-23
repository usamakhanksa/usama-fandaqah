<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemInterfaceRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'type' => 'required|in:government,payment_gateway,door_lock,erp,other',
            'status' => 'required|in:connected,disconnected,degraded,maintenance',
            'api_endpoint' => 'nullable|url',
        ];
    }
}

class StoreDataExportRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'format' => 'required|in:csv,pdf,xml,xlsx',
        ];
    }
}

class StoreServiceRequestRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:hardware,software,network,account,other',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,in_progress,waiting_on_vendor,resolved,closed',
            'assigned_to' => 'nullable|string',
        ];
    }
}
