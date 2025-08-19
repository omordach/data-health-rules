# CI/CD and Deployment Setup

This project ships with a GitHub Actions workflow that runs the test suite and, on pushes to the `main` branch, builds and deploys a container to **Google Cloud Run**.

## GitHub configuration

Create the following secrets in the repository settings:

| Secret | Description |
|--------|-------------|
| `GCP_PROJECT_ID` | Google Cloud project ID hosting the service. |
| `GCP_SA_KEY` | JSON key for a service account with deploy permissions. |
| `CLOUD_RUN_SERVICE` | Name of the Cloud Run service to deploy. |
| `CLOUD_RUN_REGION` | Region for the Cloud Run service (e.g. `us-central1`). |

## Google Cloud configuration

1. **Enable APIs** – Cloud Run, Cloud Build, Artifact Registry.
2. **Service Account** – create one and grant roles:
   - `Cloud Run Admin`
   - `Cloud Build Service Account`
   - `Artifact Registry Reader`
   - `Service Account User`
3. **Service Account Key** – generate a JSON key and save it as the `GCP_SA_KEY` GitHub secret.
4. **Cloud Run service** – create (or let the first deploy create) a service matching `CLOUD_RUN_SERVICE` in the target region.

Once configured, pushing to `main` will run tests and, if they succeed, deploy the latest container image to Cloud Run.
