import React, { useState } from 'react';

export default function ImageBlock({ block, onUpdate }) {
    const [url, setUrl] = useState(block.url || '');
    const [caption, setCaption] = useState(block.caption || '');
    const [uploading, setUploading] = useState(false);

    const handleUrlChange = (e) => {
        const newUrl = e.target.value;
        setUrl(newUrl);
        onUpdate(block.id, { url: newUrl, caption });
    };

    const handleCaptionChange = (e) => {
        const newCaption = e.target.value;
        setCaption(newCaption);
        onUpdate(block.id, { url, caption: newCaption });
    };

    const handleFileUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size must be less than 5MB');
            return;
        }

        setUploading(true);

        try {
            const formData = new FormData();
            formData.append('image', file);

            const response = await fetch('/api/lessons/upload-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) throw new Error('Upload failed');

            const data = await response.json();
            setUrl(data.url);
            onUpdate(block.id, { url: data.url, caption });
        } catch (error) {
            console.error('Upload error:', error);
            alert('Failed to upload image. Please try again.');
        } finally {
            setUploading(false);
        }
    };

    return (
        <div>
            <div className="mb-3">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    Image Source
                </label>
                <div className="flex gap-2">
                    <input
                        type="url"
                        value={url}
                        onChange={handleUrlChange}
                        placeholder="https://example.com/image.jpg or upload below"
                        className="flex-1 p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                    />
                    <label className="px-4 py-3 bg-blue-500 text-white rounded-lg cursor-pointer hover:bg-blue-600 transition-colors whitespace-nowrap">
                        {uploading ? 'Uploading...' : 'Upload'}
                        <input
                            type="file"
                            accept="image/*"
                            onChange={handleFileUpload}
                            className="hidden"
                            disabled={uploading}
                        />
                    </label>
                </div>
            </div>

            <div className="mb-3">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    Caption (Optional)
                </label>
                <input
                    type="text"
                    value={caption}
                    onChange={handleCaptionChange}
                    placeholder="Add a caption for this image..."
                    className="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                />
            </div>

            {url ? (
                <div className="border-2 border-gray-200 rounded-lg overflow-hidden">
                    <img
                        src={url}
                        alt={caption || 'Lesson image'}
                        className="w-full h-auto"
                        onError={(e) => {
                            e.target.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23f3f4f6" width="400" height="300"/%3E%3Ctext fill="%239ca3af" x="50%25" y="50%25" text-anchor="middle" dominant-baseline="middle"%3EImage failed to load%3C/text%3E%3C/svg%3E';
                        }}
                    />
                    {caption && (
                        <div className="p-3 bg-gray-50 text-sm text-gray-600 italic">
                            {caption}
                        </div>
                    )}
                </div>
            ) : (
                <div className="p-8 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg text-center text-gray-500">
                    <svg className="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>Upload an image or enter a URL</p>
                </div>
            )}
        </div>
    );
}
