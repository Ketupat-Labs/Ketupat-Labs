import React, { useState } from 'react';

export default function YouTubeBlock({ block, onUpdate }) {
    const [url, setUrl] = useState(block.url || '');

    const handleUrlChange = (e) => {
        const newUrl = e.target.value;
        setUrl(newUrl);
        onUpdate(block.id, { url: newUrl });
    };

    const getYouTubeEmbedUrl = (url) => {
        if (!url) return null;

        // Extract video ID from various YouTube URL formats
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);

        if (match && match[2].length === 11) {
            return `https://www.youtube.com/embed/${match[2]}`;
        }
        return null;
    };

    const embedUrl = getYouTubeEmbedUrl(url);

    return (
        <div>
            <div className="mb-3">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    YouTube URL
                </label>
                <input
                    type="url"
                    value={url}
                    onChange={handleUrlChange}
                    placeholder="https://www.youtube.com/watch?v=..."
                    className="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                />
            </div>

            {embedUrl ? (
                <div className="relative pt-[56.25%] bg-gray-100 rounded-lg overflow-hidden">
                    <iframe
                        className="absolute top-0 left-0 w-full h-full"
                        src={embedUrl}
                        frameBorder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                    ></iframe>
                </div>
            ) : url ? (
                <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                    ⚠️ Invalid YouTube URL. Please check the URL format.
                </div>
            ) : (
                <div className="p-4 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg text-center text-gray-500">
                    🎥 Enter a YouTube URL to preview the video
                </div>
            )}
        </div>
    );
}
