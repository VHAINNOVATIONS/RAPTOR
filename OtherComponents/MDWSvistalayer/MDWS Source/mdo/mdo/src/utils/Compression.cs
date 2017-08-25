using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Runtime.Serialization.Formatters.Binary;
using System.IO;
using System.IO.Compression;

namespace gov.va.medora.utils
{
    /// <summary>
    /// Helper class for compressing and decompressing objects
    /// </summary>
    public class Compression
    {
        /// <summary>
        /// Compress a serializable object to an array of bytes
        /// </summary>
        /// <param name="obj">The serializable object to compress</param>
        /// <returns>Binary representation of compressed object</returns>
        public byte[] compress(object obj)
        {
            BinaryFormatter bf = new BinaryFormatter();
            MemoryStream ms = new MemoryStream();
            bf.Serialize(ms, obj);

            using (MemoryStream compressedObject = new MemoryStream())
            {
                using (GZipStream gzip = new GZipStream(compressedObject, CompressionMode.Compress, true))
                {
                    gzip.Write(ms.ToArray(), 0, Convert.ToInt32(ms.Length));
                }
                return compressedObject.ToArray();
            }
        }

        /// <summary>
        /// Decompress an object compressed with the gov.va.medora.utils.Compression.compress function
        /// </summary>
        /// <param name="bytes">The binary representation of the compressed object returned by the compress function</param>
        /// <returns>The decompressed object</returns>
        public object decompress(byte[] bytes)
        {
            using (GZipStream gzip = new GZipStream(new MemoryStream(bytes), CompressionMode.Decompress))
            {
                const int bufferSize = 4096;
                byte[] buffer = new byte[bufferSize];
                using (MemoryStream decompressedObject = new MemoryStream())
                {
                    int count = 0;
                    do
                    {
                        count = gzip.Read(buffer, 0, bufferSize);
                        if (count > 0)
                        {
                            decompressedObject.Write(buffer, 0, count);
                        }
                    }
                    while (count > 0);

                    decompressedObject.Position = 0;
                    BinaryFormatter bf = new BinaryFormatter();
                    object result = bf.Deserialize(decompressedObject);
                    return result;
                }
            }
        }
        
    }
}
