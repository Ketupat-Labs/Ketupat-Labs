# 2.2.7. Use Case UC007: Attempt Lesson

### Table 2.7.: Use Case Description for Attempt Lesson

| Field | Description |
| :--- | :--- |
| **Use Case** | Attempt Lesson |
| **ID** | UC007 |
| **Actors** | Student |
| **Preconditions** | 1. Lesson must exist and be published (UC003).<br>2. Student must be logged in.<br>3. Student is enrolled in the class or Lesson is Public. |
| **Main Flow** | **1. Student Access:**<br>1. Student navigates to "Senarai Pelajaran" (Lesson List).<br>2. Student clicks on a specific Lesson title.<br>3. System displays the Lesson Details page containing content blocks (Text, Images, Interactive Games).<br><br>**2. Interactive Game Interaction (if present):**<br>1. Student views the **Interactive Game** (Memory Game / Quiz) embedded in the lesson.<br>2. System initializes the game board or questions.<br>3. Student attempts the game (e.g., matching cards or selecting answers).<br>4. System provides immediate feedback (e.g., card flip, correct/wrong indicator).<br>5. Student completes the game.<br>6. **System automatically saves the score** and displays a success message (e.g., "Keputusan Disimpan Secara Automatik").<br><br>**3. Practical Submission (if assignment requires file):**<br>1. Student scrolls to the **Tugasan / Penyerahan** (Submission) section.<br>2. Student clicks on the upload area or "Choose File" button.<br>3. Student selects a file from their device.<br>4. Student clicks "Hantar Tugas" (Submit).<br>5. System validates the file type and size.<br>6. System stores the file and updates status to "Submitted Awaiting Grade". |
| **Postconditions** | 1. Lesson progress is updated (Status: Selesai).<br>2. Game score is recorded and visible in "Lihat Prestasi".<br>3. Submitted file is available for Teacher to grade. |
| **Alternative Flows** | **A1: Retry Activity**<br>1. Student decides to play the game again to improve score.<br>2. System resets the game.<br>3. New score is auto-saved upon completion.<br><br>**A2: View Performance**<br>1. Student clicks "Lihat Prestasi" from the dashboard.<br>2. System shows the updated grade for the lesson.<br><br>**A3: Cancel Submission**<br>1. Student decides not to upload a file.<br>2. System performs no action on the submission status. |
| **Exception Flows** | **E1: Invalid File Type**<br>1. Student uploads a restricted file type (e.g., .exe).<br>2. System rejects upload and displays error "Format fail tidak disokong".<br><br>**E2: Network Failure on Auto-Save**<br>1. Student finishes game but internet is disconnected.<br>2. System displays error "Ralat rangkaian" and prompts user to ensure connection. |

---

## User Stories

### 2.2.7.1. User Story US007-01: Attempt Interactive Game
**As a** student,  
**I want to** play interactive games (Memory Game or Quiz) directly within the lesson content,  
**So that** I can reinforce my understanding of the topic in an engaging way and earn marks immediately.

**Acceptance Criteria:**
*   Ability to load the game (Memory or Quiz) embedded within the Lesson View.
*   Ability to interact with game elements (flip cards, select quiz answers).
*   Ability to see immediate visual feedback (correct matches, scores).
*   **System must automatically save the score** to the database upon completion.
*   Ability to retry the game multiple times (updates latest/best score).

### 2.2.7.2. User Story US007-02: Submit Practical Exercise
**As a** student,  
**I want to** upload a file for a practical exercise (e.g., coding file or screenshot),  
**So that** my teacher can review and grade my work.

**Acceptance Criteria:**
*   Ability to see a file upload interface on the Lesson page (if assignment exists).
*   Ability to select files with specific extensions (.png, .jpg, .html, .zip).
*   System must validate file size (e.g., max 10MB).
*   Ability to change submission status to "Submitted Awaiting Grade" upon success.
*   Ability to view the submitted file status.

### 2.2.7.3. User Story US007-03: View Lesson Content
**As a** student,  
**I want to** read and view the content blocks (Text, Headers, Images, Videos) of a lesson,  
**So that** I can learn the material before attempting the activities.

**Acceptance Criteria:**
*   Ability to view the "Tajuk" (Title) and "Topik" (Topic) of the lesson.
*   Ability to read standard text content.
*   Ability to view embedded images or educational videos.
*   Content should be presented in a clean, readable layout.

### 2.2.7.4. User Story US007-04: Track Lesson Progress
**As a** student,  
**I want to** see which lessons I have completed and my grades,  
**So that** I can know what to focus on next.

**Acceptance Criteria:**
*   Ability to see a "Status" (e.g., Selesai / Belum Selesai) on the Performance Dashboard.
*   Ability to see the specific percentage score for interactive games.
*   Ability to see "100%" or specific grades for completed lessons.
