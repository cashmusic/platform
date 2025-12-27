- name: Set up JDK 21
  uses: actions/setup-java@v4
  with:
    distribution: 'temurin'
    java-version: '21'

- name: Setup Node.js environment (pin to supported)
  uses: actions/setup-node@v4
  with:
    node-version: '18.15.0'

- name: Ensure workspaceStorage exists
  shell: pwsh
  run: |
    New-Item -ItemType Directory -Force -Path ./test-resources/settings/User/workspaceStorage | Out-Null

- name: Pre-test cleanup (kill common processes + safe-delete temp dirs)
  shell: pwsh
  run: |
    # Kill likely processes that can hold locks
    Get-Process -Name java,mvn,Code -ErrorAction SilentlyContinue | ForEach-Object { try { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue } catch {} }

    # Safe-delete patterns (retry loop) to avoid EBUSY on Windows
    $paths = @(
      "$env:TEMP\vscode-java-dependency-ui-test*",
      "$PWD\test-resources\settings\User\workspaceStorage"
    )
    foreach ($p in $paths) {
      for ($i=0; $i -lt 6; $i++) {
        try {
          Get-ChildItem -Path $p -Force -ErrorAction Stop | Remove-Item -Recurse -Force -ErrorAction Stop
          break
        } catch {
          Start-Sleep -s 2
        }
      }
    }

- name: Install Node.js modules
  run: npm install

- name: Install VSCE
  run: npm install -g vsce

- name: Lint
  run: npm run tslint

- name: Checkstyle
  working-directory: .\jdtls.ext
  run: .\mvnw.cmd checkstyle:check

- name: Build OSGi bundle
  run: npm run build-server

- name: Build VSIX file
  run: vsce package

- name: UI Test
  continue-on-error: true
  id: test
  env:
    # Increase timeouts for test runner if supported by test scripts
    MOCHA_TIMEOUT: 300000
    CI: true
  run: npm run test-ui

- name: Retry UI Test 1 (with cleanup)
  continue-on-error: true
  if: steps.test.outcome == 'failure'
  id: retry1
  run: |
    git reset --hard
    git clean -fd
  shell: pwsh
- name: Retry UI Test 1 - run tests
  continue-on-error: true
  if: steps.test.outcome == 'failure'
  id: retry1_run
  env:
    MOCHA_TIMEOUT: 300000
    CI: true
  run: npm run test-ui

- name: Retry UI Test 2 (with cleanup)
  continue-on-error: true
  if: steps.retry1_run.outcome == 'failure'
  id: retry2
  run: |
    git reset --hard
    git clean -fd
  shell: pwsh
- name: Retry UI Test 2 - run tests
  continue-on-error: true
  if: steps.retry1_run.outcome == 'failure'
  id: retry2_run
  env:
    MOCHA_TIMEOUT: 300000
    CI: true
  run: npm run test-ui

- name: Final safe cleanup (attempt to remove temp/test dirs to avoid EBUSY in subsequent steps)
  if: always()
  shell: pwsh
  run: |
    Get-Process -Name java,mvn,Code -ErrorAction SilentlyContinue | ForEach-Object { try { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue } catch {} }
    $paths = @(
      "$env:TEMP\vscode-java-dependency-ui-test*",
      "$PWD\test-resources\settings\User\workspaceStorage"
    )
    foreach ($p in $paths) {
      for ($i=0; $i -lt 6; $i++) {
        try {
          Get-ChildItem -Path $p -Force -ErrorAction Stop | Remove-Item -Recurse -Force -ErrorAction Stop
          break
        } catch {
          Start-Sleep -s 2
        }
      }
    }

- name: Set test status
  if: ${{ steps.test.outcome == 'failure' && steps.retry1_run.outcome == 'failure' && steps.retry2_run.outcome == 'failure' }}
  run: |
    echo "Tests failed"
    exit 1

- name: Print language server Log if job failed
  if: ${{ failure() }}
  run: Get-ChildItem -Path ./test-resources/settings/User/workspaceStorage/*/redhat.java/jdt_ws/.metadata/.log | cat