<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use App\Models\Categorie;
use App\Models\Commentaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'totalUsers' => User::count(),
            'totalServices' => Service::count(),
            'totalCategories' => Categorie::count(),
            'totalCommentaires' => Commentaire::count()
        ]);
    }

    public function charts(): JsonResponse
    {
        return response()->json([
            'monthlyStats' => $this->getMonthlyStats(),
            'categoryStats' => $this->getCategoryStats()
        ]);
    }

    public function cumulativeGrowth(): JsonResponse
    {
        return response()->json($this->getCumulativeGrowthData());
    }

    public function topCategories(): JsonResponse
    {
        return response()->json($this->getTopCategories());
    }

    public function topCommentedServices(): JsonResponse
    {
        return response()->json($this->getTopCommentedServices());
    }

    protected function getMonthlyStats(): array
    {
        $stats = ['labels' => [], 'users' => [], 'services' => []];
        $now = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $stats['labels'][] = $date->format('M Y');
            
            $stats['users'][] = User::whereBetween('created_at', [
                $date->startOfMonth(),
                $date->endOfMonth()
            ])->count();

            $stats['services'][] = Service::whereBetween('created_at', [
                $date->startOfMonth(),
                $date->endOfMonth()
            ])->count();
        }

        return $stats;
    }

    protected function getCategoryStats(): array
    {
        $categories = Categorie::query()
            ->withCount('services')
            ->get();

        return [
            'labels' => $categories->pluck('name'),
            'counts' => $categories->pluck('services_count')
        ];
    }

    protected function getCumulativeGrowthData(): array
    {
        $data = ['labels' => [], 'cumulativeUsers' => [], 'cumulativeServices' => []];
        $totalUsers = 0;
        $totalServices = 0;
        $now = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $data['labels'][] = $date->format('M Y');
            
            $users = User::whereBetween('created_at', [
                $date->startOfMonth(),
                $date->endOfMonth()
            ])->count();

            $services = Service::whereBetween('created_at', [
                $date->startOfMonth(),
                $date->endOfMonth()
            ])->count();

            $totalUsers += $users;
            $totalServices += $services;

            $data['cumulativeUsers'][] = $totalUsers;
            $data['cumulativeServices'][] = $totalServices;
        }

        return $data;
    }

    protected function getTopCategories(): array
    {
        $categories = Categorie::query()
            ->withCount('services')
            ->orderByDesc('services_count')
            ->take(5)
            ->get();

        return [
            'labels' => $categories->pluck('name'),
            'counts' => $categories->pluck('services_count')
        ];
    }

    protected function getTopCommentedServices(): array
    {
        $services = Service::query()
            ->withCount('commentaires')
            ->orderByDesc('commentaires_count')
            ->take(5)
            ->get();

        return [
            'labels' => $services->pluck('title'),
            'counts' => $services->pluck('commentaires_count')
        ];
    }
}