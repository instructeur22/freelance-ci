import { Suspense } from "react";
import RegisterForm from "./form";

export default function RegisterPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-[70vh] flex items-center justify-center">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      }
    >
      <RegisterForm />
    </Suspense>
  );
}
