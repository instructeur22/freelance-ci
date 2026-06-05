"use client";

export default function ClientProjectQuotesPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };
  // Redirect to project detail page which already shows quotes
  return (
    <div className="text-center py-12">
      <p>Redirection...</p>
      <meta httpEquiv="refresh" content={`0;url=/dashboard/client/projects/${id}`} />
    </div>
  );
}
