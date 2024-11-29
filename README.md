# Hellō identity provider(IdP) client in PHP

![Run PHP Tests](https://github.com/UnnikrishnanBhargavakurup/hellocoop/actions/workflows/php-tests.yml/badge.svg)

### Code Quality: Pre-Commit Hook for Linting

To maintain code quality in this project, we use a pre-commit hook for automatic linting of code before each commit. To set up the pre-commit hook, follow the steps below:

1. **Copy the pre-commit hook to your Git hooks directory**:

   Run the following command to copy the `pre-commit` hook to the `.git/hooks/` folder:

   ```bash
   cp pre-commit .git/hooks/
   ```

2. **Make the hook executable** (if it’s not already):

   ```bash
   chmod +x .git/hooks/pre-commit
   ```

This will ensure that the hook runs automatically whenever you try to commit your code, helping maintain consistent code quality across the project.

