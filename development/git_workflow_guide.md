# Git Workflow for Your Project (Optional but Recommended)

## Why Use Git?

- ✅ Track all changes (history)
- ✅ Undo mistakes easily
- ✅ See what changed between versions
- ✅ Professional development practice
- ✅ Backup your code

---

## Quick Setup (5 minutes)

### Step 1: Install Git
Download from: https://git-scm.com/download/win

### Step 2: Initialize Repository
Open terminal in VSCode (Ctrl + `):
```bash
git init
git config user.name "Your Name"
git config user.email "your.email@company.com"
```

### Step 3: Create .gitignore
Create a file named `.gitignore` in your project root:
```
# Exclude documentation (not needed in production)
*.md
LEARNING_GUIDE.md
project_structure.md
database_documentation.md

# Exclude deployment scripts
deploy_to_xampp.bat
exclude.txt

# Exclude system files
.DS_Store
Thumbs.db
```

### Step 4: First Commit
```bash
git add .
git commit -m "Initial commit: Database schema and config"
```

---

## Daily Workflow

### After completing a feature:
```bash
# See what changed
git status

# Add files to commit
git add .

# Commit with message
git commit -m "Added login system"

# Deploy to XAMPP (manual copy or script)
# Then test in browser
```

---

## Useful Commands

```bash
# See all commits (history)
git log --oneline

# See what changed in a file
git diff filename.php

# Undo changes (before commit)
git checkout -- filename.php

# Go back to previous version
git checkout <commit-id>

# Create a backup branch
git branch backup-before-changes
```

---

## Benefits for Your 7-Day Project

- ✅ **Day 1**: Commit "Database and auth setup"
- ✅ **Day 2**: Commit "Asset management complete"
- ✅ **Day 3**: Commit "User loan request working"
- ✅ **Day 4**: Commit "Approval workflow done"
- ✅ **Day 5**: Commit "Return process complete"
- ✅ **Day 6**: Commit "UI improvements"
- ✅ **Day 7**: Commit "Final version - ready for production"

If something breaks, you can always go back to a working version!

---

## Not Required But Highly Recommended

Git is industry standard. Learning it now will benefit your entire career.
But for your 7-day deadline, you can skip this and use Option 2 (Symbolic Link) instead.
