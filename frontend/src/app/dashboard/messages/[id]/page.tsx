"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { messageApi } from "@/lib/api/endpoints/messages";
import { formatRelativeDate } from "@/lib/utils/format";
import Link from "next/link";
import { useAuth } from "@/hooks/useAuth";
import { useState, useEffect, useRef } from "react";
import { supabase } from "@/lib/supabase/client";

export default function ConversationDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const [newMessage, setNewMessage] = useState("");
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const { data, isLoading } = useQuery({
    queryKey: ["conversation", id],
    queryFn: () => messageApi.messages(id),
  });

  const sendMutation = useMutation({
    mutationFn: (content: string) =>
      messageApi.sendMessage(id, { content }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["conversation", id] });
      setNewMessage("");
    },
  });

  // Real-time subscription via Supabase Realtime
  useEffect(() => {
    const channel = supabase
      .channel(`conversation:${id}`)
      .on(
        "postgres_changes",
        {
          event: "INSERT",
          schema: "public",
          table: "messages",
          filter: `conversation_id=eq.${id}`,
        },
        () => {
          queryClient.invalidateQueries({
            queryKey: ["conversation", id],
          });
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [id, queryClient]);

  // Auto-scroll to bottom
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [data]);

  const messages = data?.data ?? [];

  // Determine the other participant name from conversation context
  // For now use placeholder - actual data comes from conversation endpoint
  const otherName = "Participant";

  const handleSend = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim()) return;
    sendMutation.mutate(newMessage.trim());
  };

  return (
    <div className="max-w-3xl mx-auto h-[calc(100vh-12rem)] flex flex-col">
      {/* Header */}
      <div className="flex items-center gap-3 pb-4 border-b border-base-200">
        <Link
          href="/dashboard/messages"
          className="btn btn-ghost btn-sm btn-square"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </Link>
        <div className="avatar placeholder">
          <div className="w-8 h-8 rounded-full bg-primary/10 text-primary text-sm font-medium">
            {otherName.charAt(0)}
          </div>
        </div>
        <div>
          <h1 className="font-semibold text-sm">{otherName}</h1>
        </div>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto py-4 space-y-3">
        {isLoading ? (
          <div className="flex items-center justify-center py-12">
            <span className="loading loading-spinner loading-lg text-primary" />
          </div>
        ) : messages.length === 0 ? (
          <div className="text-center py-12 text-base-content/50">
            <p>Aucun message. Envoyez le premier message !</p>
          </div>
        ) : (
          messages.map((msg) => {
            const isMine = msg.sender_id === user?.id;
            return (
              <div
                key={msg.id}
                className={`flex ${isMine ? "justify-end" : "justify-start"}`}
              >
                <div
                  className={`max-w-[80%] rounded-2xl px-4 py-2.5 ${
                    isMine
                      ? "bg-primary text-primary-content rounded-br-md"
                      : "bg-base-200 text-base-content rounded-bl-md"
                  }`}
                >
                  <p className="text-sm whitespace-pre-wrap">{msg.content}</p>
                  <div
                    className={`flex items-center gap-1 mt-1 ${
                      isMine
                        ? "justify-end"
                        : "justify-start"
                    }`}
                  >
                    <span
                      className={`text-[10px] ${
                        isMine
                          ? "text-primary-content/60"
                          : "text-base-content/40"
                      }`}
                    >
                      {formatRelativeDate(msg.created_at)}
                    </span>
                    {isMine && (
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className={`w-3 h-3 ${
                          msg.status === "read"
                            ? "text-primary-content/80"
                            : "text-primary-content/40"
                        }`}
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                      </svg>
                    )}
                  </div>
                </div>
              </div>
            );
          })
        )}
        <div ref={messagesEndRef} />
      </div>

      {/* Input */}
      <form
        onSubmit={handleSend}
        className="flex items-center gap-2 pt-4 border-t border-base-200"
      >
        <input
          value={newMessage}
          onChange={(e) => setNewMessage(e.target.value)}
          placeholder="Écrivez votre message..."
          className="input input-bordered flex-1"
          disabled={sendMutation.isPending}
        />
        <button
          type="submit"
          disabled={!newMessage.trim() || sendMutation.isPending}
          className="btn btn-primary"
        >
          {sendMutation.isPending ? (
            <span className="loading loading-spinner loading-sm" />
          ) : (
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
              />
            </svg>
          )}
        </button>
      </form>
    </div>
  );
}
