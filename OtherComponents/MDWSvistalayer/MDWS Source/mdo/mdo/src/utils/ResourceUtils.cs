using System;
using System.Collections.Generic;
using System.Text;
using System.IO;
using System.Reflection;

namespace gov.va.medora.utils
{
    public static class ResourceUtils
    {
        static string _resourcesPath;

        /// <summary>
        /// Finds and returns the projects full 'resources' directory path (always ends with '\')
        /// </summary>
        public static string ResourcesPath
        {
            // get resources path once and store it
            get 
            {
                if (String.IsNullOrEmpty(_resourcesPath))
                {
                    _resourcesPath = getResources();
                    return _resourcesPath;
                }
                else return _resourcesPath;
            }
        }

        public static string XmlResourcesPath
        {
            get { return Path.Combine(ResourcesPath, "xml"); }
        }

        public static string DataResourcesPath
        {
            get { return Path.Combine(ResourcesPath, "data"); }
        }


        /// <summary>
        /// Recurse up the directory tree searching for the resources folder 
        /// (always with a trailing '\' character)
        /// </summary>
        /// <returns>
        /// A string representation of the project's resource 
        /// path (always ending with a '\' character) or null if not found
        /// </returns>
        private static string getResources()
        {
            try
            {
                string current = System.IO.Path.GetDirectoryName(
                    Assembly.GetExecutingAssembly().GetName().CodeBase);
                current = current.Replace("file:\\", "");
                return getResources(current);
            }
            catch (Exception)
            {
                return null;
            }
        }

        /// <summary>
        /// This function expects the following convention:
        ///     1. resources folder is not under the "bin\" directory
        /// </summary>
        /// <param name="current">current working directory</param>
        /// <returns>string of resource directory matching above conventions</returns>
        static string getResources(string current)
        {
            try
            {
                DirectoryInfo di = new DirectoryInfo(current);
                DirectoryInfo[] dirs = di.GetDirectories("resources*", SearchOption.TopDirectoryOnly);
                if (dirs == null || dirs.Length == 0 || dirs.Length != 1)
                {
                    di = di.Parent;
                    if (di.Parent == null) // at root of drive
                    {
                        return null;
                    }
                    return getResources(di.FullName);
                }
                else // found it!
                {
                    if (dirs[0].FullName.Contains("\\bin\\")) // if we're in bin directory, keep recursing up - TBD: should we use this convention?
                    {
                        return getResources(di.Parent.FullName);
                    }
                    if (dirs[0].FullName.EndsWith("\\"))
                    {
                        return dirs[0].FullName; 
                    }
                    else return dirs[0].FullName + "\\";
                }
            }
            catch (Exception)
            {
                return null;
            }
        }
    }
}
