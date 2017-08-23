using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using System.Runtime.CompilerServices;

namespace gov.va.medora.utils
{
    public class FileIOUtils
    {
        /// <summary>
        /// Saves string to the specified file, appending if the file exists
        /// </summary>
        /// <param name="path">Full path to file</param>
        /// <param name="response">Response to write to file</param>
        public static void writeToFile(string path, string data, Boolean append = false)
        {
            FileMode mode = append ? FileMode.Append : FileMode.OpenOrCreate;
            using (FileStream fileStream = new FileStream(path, mode, FileAccess.Write, FileShare.None))
            {
                using (StreamWriter streamWriter = new StreamWriter(fileStream))
                {
                    streamWriter.Write(data);
                }
            }
            //fileStream.Close();
        }

        /// <summary>
        /// Saves and array of strings to the specified file, appending if the file exists
        /// </summary>
        /// <param name="path">Full path to file</param>
        /// <param name="response">Array of strings to write to file</param>
        public static void writeToFile(string path, string[] data, Boolean append = false)
        {
            FileMode mode = append ? FileMode.Append : FileMode.OpenOrCreate;
            //FileStream fileStream = new FileStream(path, mode, FileAccess.Write, FileShare.None);
            using (FileStream fileStream = new FileStream(path, mode, FileAccess.Write, FileShare.None))
            {
                using (StreamWriter streamWriter = new StreamWriter(fileStream))
                {
                    for (int i = 0; i < data.Length; i++)
                    {
                        streamWriter.WriteLine(data[i]);
                    }
                }
            }
            //fileStream.Close();
        }

        /// <summary>
        /// Reads the specified file returning the content
        /// </summary>
        /// <param name="path">Full path of file</param>
        /// <returns>string representation of the file</returns>
        public static string readFromFile(string path)
        {
            string content = "";

            using (FileStream fileStream = new FileStream(path, FileMode.Open, FileAccess.Read, FileShare.None))
            {
                using (StreamReader rdr = new StreamReader(fileStream))
                {
                    content = rdr.ReadToEnd();
                }
            }
            //fileStream.Close();
            return content;
        }
    }
}
