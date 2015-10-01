using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.IO;

namespace gov.va.medora.mdo.dao.file
{
    public class ConfigFileDao
    {
        string _filePath;

        /// <summary>
        /// ConfigFileDao constructor
        /// </summary>
        /// <param name="filePath">The full path to the config file</param>
        /// <example>ConfigFileDao configDao = new ConfigFileDao(@"C:\inetpub\wwwroot\MDWS\resources\config\MDWS.conf");</example>
        public ConfigFileDao(string filePath)
        {
            _filePath = filePath;
        }

        /// <summary>
        /// Seek out a config file value
        /// </summary>
        /// <param name="key">The config file key</param>
        /// <returns></returns>
        public string getValue(string key, string section)
        {
            Dictionary<string, Dictionary<string, string>> kvps = getAllValues();
            return kvps[section][key];
        }

        /// <summary>
        /// Get all config file values
        /// </summary>
        /// <returns></returns>
        public Dictionary<string, Dictionary<string, string>> getAllValues()
        {
            Dictionary<string, Dictionary<string, string>> kvps = new Dictionary<string, Dictionary<string, string>>();
            using (StreamReader sr = new StreamReader(
                new FileStream(_filePath, FileMode.Open, FileAccess.Read, FileShare.ReadWrite)))
            {
                string currentLine = "";
                string currentConfigSection = "";
                while (!sr.EndOfStream)
                {
                    currentLine = sr.ReadLine();
                    if (String.IsNullOrEmpty(currentLine) || String.IsNullOrEmpty(currentLine.Trim()))
                    {
                        continue;
                    }
                    if (currentLine.StartsWith("//")) // allow line comments
                    {
                        continue;
                    }
                    if (currentLine.StartsWith("["))
                    {
                        currentLine = currentLine.Replace("[", "");
                        currentLine = currentLine.Replace("]", "");
                        currentLine = currentLine.Trim();
                        if (!kvps.ContainsKey(currentLine))
                        {
                            kvps.Add(currentLine, new Dictionary<string, string>());
                            currentConfigSection = currentLine;
                        }
                        continue;
                    }
                    if (String.IsNullOrEmpty(currentConfigSection))
                    {
                        continue;
                    }
                    string[] pieces = currentLine.Split(new char[] { '=' }, 2);
                    if (pieces == null || pieces.Length != 2 || kvps.ContainsKey(pieces[0]))
                    {
                        continue; // invalid config line
                    }
                    kvps[currentConfigSection].Add(pieces[0].Trim(), pieces[1].Trim());
                }
            }
            return kvps;
        }

        /// <summary>
        /// Update or add a key and value to the config file
        /// </summary>
        /// <param name="key">Config key</param>
        /// <param name="value">Config value</param>
        /// <param name="section">Config section</param>
        public void updateValue(string key, string value, string section)
        {
            Dictionary<string, Dictionary<string, string>> kvps = getAllValues();
            if (!kvps.ContainsKey(section))
            {
                kvps.Add(section, new Dictionary<string, string>());
            }
            if (!kvps[section].ContainsKey(key))
            {
                kvps[section].Add(key, value);
            }
            else
            {
                kvps[section][key] = value;
            }
            writeToFile(kvps);
        }

        /// <summary>
        /// Write a dictionary of config/value pairs to the config file
        /// </summary>
        /// <param name="kvps">List of Key/Value pairs</param>
        public void writeToFile(Dictionary<string, Dictionary<string, string>> kvps)
        {
            using (StreamWriter sw = new StreamWriter(
                new FileStream(_filePath, FileMode.Create, FileAccess.Write, FileShare.ReadWrite)))
            {
                foreach (string s in kvps.Keys)
                {
                    sw.WriteLine("[" + s + "]");
                    foreach (string configKey in kvps[s].Keys)
                    {
                        sw.WriteLine(configKey + " = " + kvps[s][configKey]);
                    }
                    sw.WriteLine();
                }
            }
        }

        /// <summary>
        /// Remove a configuration item from the config file
        /// </summary>
        /// <param name="key">The config item key</param>
        public void removeFromFile(string key, string section)
        {
            Dictionary<string, Dictionary<string, string>> kvps = getAllValues();
            if (kvps.ContainsKey(section))
            {
                if (kvps[section].ContainsKey(key))
                {
                    kvps[section].Remove(key);
                }
            }
            writeToFile(kvps);
        }
    }
}