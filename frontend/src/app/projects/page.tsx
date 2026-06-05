import { Suspense } from "react";
import ProjectsContent from "./content";

export default function ProjectsPage() {
  return (
    <Suspense
      fallback={
        <div className="container mx-auto px-4 py-12">
          <div className="flex items-center justify-center min-h-[40vh]">
            <span className="loading loading-spinner loading-lg text-primary" />
          </div>
        </div>
      }
    >
      <ProjectsContent />
    </Suspense>
  );
}
