// App.js
import Chatbot from './Chatbot';
function App() {
    // This centers the chatbot for the demo
    return (
        <div style={{ display: 'grid', placeItems: 'center', minHeight: '100vh' }}>
            <Chatbot />
        </div>
    );
}
export default App;