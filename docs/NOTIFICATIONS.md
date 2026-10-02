# In-app notifications

Implemented 2026-10-02 (Asia/Jakarta).

- Authenticated JSON endpoints: GET /notifications (10 items/page), PATCH
  /notifications/{uuid}/read, PATCH /notifications/read-all.
- All queries are scoped to the authenticated user's notifications relation.
  Superadmin does not bypass ownership. Mutations use web CSRF protection and throttling.
- Data uses Laravel's standard notifications table and Notifiable relationship.
- Core Notification RecordNotification writes database-only messages in the same
  transaction as workspace creation or draft-plan save. No email/WhatsApp is sent.
- Recipients are the workspace owner and the draft-plan actor respectively.
  Historical events are not backfilled; existing accounts may initially see an empty list.
- The bell fetches on mount/open/navigation, every 60 seconds while the tab is visible,
  and when the tab becomes visible. It is polling, not WebSocket push.
- Panel includes unread count, pages, per-item/read-all actions, empty/loading/error
  states, retry, Escape/outside close, focus management, responsive light/dark styling.
- Account deletion removes that account's notifications inside the deletion transaction.
- Email verification notifications keep their existing independent delivery behavior.

After pulling: run the new migration before using workspace/plan mutations.
npm run build/dev still needs explicit tool approval from the user.
