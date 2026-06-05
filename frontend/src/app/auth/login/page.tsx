import { Suspense } from "react";
import LoginForm from "./form";

export default function LoginPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-[70vh] flex items-center justify-center">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      }
    >
      <LoginForm />
    </Suspense>
  );
}
