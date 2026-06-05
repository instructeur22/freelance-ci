import Link from "next/link";

export function Logo({ showText = true, size = "sm", className = "" }: { showText?: boolean; size?: "sm" | "md" | "lg"; className?: string }) {
  const imgSize = size === "lg" ? 48 : size === "md" ? 40 : 36;
  const textSize = size === "lg" ? "text-2xl" : size === "md" ? "text-lg" : "text-base";

  return (
    <Link href="/" className={`flex items-center gap-2 ${className}`}>
      <img src="/Freelance-CI.jpeg" alt="Freelance CI" width={imgSize} height={imgSize} className="rounded-lg object-cover" />
      {showText && (
          <span className={`font-bold ${textSize} hidden sm:inline`}>
            reelance<span className="text-primary">CI</span>
          </span>
      )}
    </Link>
  );
}
