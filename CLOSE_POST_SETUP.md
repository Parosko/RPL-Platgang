# Close Post Feature - Setup Instructions

## Database Migration Required

Before using the close post feature, run the following SQL command to add the `closed_at` column to the `peluang` table:

```sql
ALTER TABLE peluang ADD COLUMN closed_at TIMESTAMP NULL DEFAULT NULL AFTER created_at;
```

**Or** run the migration file:
```
mysql -u root platform_karir_kampus < migrations/add_closed_at_to_peluang.sql
```

## Feature Overview

### For Mitra:
1. Go to "Postingan Saya" (My Posts)
2. Each open posting will show a "Tutup Posting" (Close Post) button
3. Click the button to close the posting
4. A confirmation dialog will appear asking if they want to proceed
5. When closing:
   - The post status changes to "Closed"
   - All non-accepted applicants are automatically rejected
   - Rejected applicants receive a notification

### What Happens When Post is Closed:
- Status changes from "Open" to "Closed"
- All "pending" applicants → automatically rejected
- All "rejected" applicants → stay rejected
- Only "accepted" applicants keep their status
- Each rejected applicant gets a notification

### In Dashboard:
- Closed posts show "Closed" badge
- The "Daftar" (Apply) button is disabled/hidden for closed posts
- Closed posts are read-only

### Post Requirements:
- Currently only affects pending applicants
- Accepted positions remain unaffected
- This is useful when mitra has enough applicants and wants to stop receiving new applications

## Files Modified:
- `views/mitra/my_posts.php` - Added close button logic
- `views/components/mitra_post_card.php` - New component with close button
- `views/components/post_card.php` - Updated to check for closed status
- `controllers/mitra/close_post_process.php` - Backend processor
- `migrations/add_closed_at_to_peluang.sql` - Database migration
