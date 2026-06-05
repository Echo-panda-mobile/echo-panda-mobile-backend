import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

type TopRecommendedSong = { song_id: number; title: string | null; plays: number };
type SuccessfulReason = { reason: string; total: number };
type DailyRecommendationEvent = { day: string; shown: number; clicked: number; played: number };

type MetricsPageProps = {
  metrics: {
    total_recommendations_served: number;
    recommendation_click_rate: number;
    recommendation_play_rate: number;
    top_recommended_songs: TopRecommendedSong[];
    most_successful_reasons: SuccessfulReason[];
    daily: DailyRecommendationEvent[];
  };
};

export default function RecommendationsAnalytics() {
  const { metrics } = usePage().props as unknown as PageProps<MetricsPageProps>;

  return (
    <AuthenticatedLayout header="Recommendation Analytics">
      <Head title="Recommendation Analytics" />

      <div className="mx-auto max-w-7xl space-y-6 px-4 py-6 text-white">
        <section className="grid gap-4 md:grid-cols-3">
          <div className="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <div className="text-xs uppercase tracking-widest text-slate-400">Total Recommendations Served</div>
            <div className="mt-2 text-3xl font-black">{metrics.total_recommendations_served.toLocaleString()}</div>
          </div>
          <div className="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <div className="text-xs uppercase tracking-widest text-slate-400">Click Rate</div>
            <div className="mt-2 text-3xl font-black">{metrics.recommendation_click_rate}%</div>
          </div>
          <div className="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <div className="text-xs uppercase tracking-widest text-slate-400">Play Rate</div>
            <div className="mt-2 text-3xl font-black">{metrics.recommendation_play_rate}%</div>
          </div>
        </section>

        <section className="grid gap-6 lg:grid-cols-2">
          <div className="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <h2 className="text-lg font-bold">Top Recommended Songs</h2>
            <div className="mt-4 space-y-2">
              {metrics.top_recommended_songs.map((row: TopRecommendedSong) => (
                <div key={row.song_id} className="flex items-center justify-between rounded-lg border border-white/5 px-3 py-2">
                  <span className="text-sm text-slate-200">{row.title || `Song #${row.song_id}`}</span>
                  <span className="text-sm text-cyan-300">{row.plays} plays</span>
                </div>
              ))}
            </div>
          </div>

          <div className="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <h2 className="text-lg font-bold">Most Successful Reasons</h2>
            <div className="mt-4 space-y-2">
              {metrics.most_successful_reasons.map((row: SuccessfulReason, idx: number) => (
                <div key={`${row.reason}-${idx}`} className="flex items-center justify-between rounded-lg border border-white/5 px-3 py-2">
                  <span className="text-sm text-slate-200">{row.reason}</span>
                  <span className="text-sm text-fuchsia-300">{row.total}</span>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section className="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
          <h2 className="text-lg font-bold">Daily Recommendation Events</h2>
          <div className="mt-4 overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead>
                <tr className="border-b border-white/10 text-slate-400">
                  <th className="py-2">Day</th>
                  <th className="py-2">Shown</th>
                  <th className="py-2">Clicked</th>
                  <th className="py-2">Played</th>
                </tr>
              </thead>
              <tbody>
                {metrics.daily.map((row: DailyRecommendationEvent) => (
                  <tr key={row.day} className="border-b border-white/5">
                    <td className="py-2">{row.day}</td>
                    <td className="py-2">{row.shown}</td>
                    <td className="py-2">{row.clicked}</td>
                    <td className="py-2">{row.played}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </AuthenticatedLayout>
  );
}
