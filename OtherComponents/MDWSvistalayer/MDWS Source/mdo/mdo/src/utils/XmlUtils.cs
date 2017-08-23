using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Xml;

namespace gov.va.medora.mdo.src.utils
{
    public static class XmlUtils
    {
        /// <summary>
        /// Get an attribute from an XmlNode
        /// </summary>
        /// <param name="node"></param>
        /// <param name="attributeName"></param>
        /// <returns></returns>
        public static string getXmlAttributeValue(XmlNode node, string xPath, string attributeName)
        {
            if (node == null || node.SelectSingleNode(xPath) == null)
            {
                return null;
            }

            XmlNode selectedNode = null;
            if (String.IsNullOrEmpty(xPath) || String.Equals(xPath, "/"))
            {
                selectedNode = node;
            }
            else
            {
                selectedNode = node.SelectSingleNode(xPath);
            }
            if (selectedNode.Attributes == null || selectedNode.Attributes.Count == 0 || selectedNode.Attributes[attributeName] == null)
            {
                return null;
            }
            return selectedNode.Attributes[attributeName].Value;
        }
    }
}
