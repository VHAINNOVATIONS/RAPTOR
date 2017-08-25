using System;
using System.Collections.Generic;
using System.Text;
using System.Net;
using System.Net.Sockets;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.http
{
    public class HttpConnection
    {
        string hostname;
        string path;
        int port;
        const int CONNECTION_TIMEOUT = 60000;
        Socket socket;

        public HttpConnection(string hostname, string path, int port)
        {
            this.hostname = hostname;
            this.path = path;
            this.port = port;
        }

        public string[] send(string msg)
        {
            IPAddress addr = null;
            addr = (IPAddress)Dns.GetHostEntry(hostname).AddressList[0];
            IPEndPoint endPoint = new IPEndPoint(addr, port);
            socket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
            socket.SetSocketOption(SocketOptionLevel.Socket, SocketOptionName.ReceiveTimeout, CONNECTION_TIMEOUT);
            socket.Connect(endPoint);
            if (!socket.Connected)
            {
                throw new ConnectionException("Unable to connect to " + hostname + ", port " + port);
            }

            string reply = "";
            try
            {
                reply = query(msg);
            }
            catch (SocketException se)
            {
                throw new ConnectionException("No HTTP listener at " + hostname, se);
            }
            finally
            {
                socket.Close();
            }
            string[] result = StringUtils.split(reply, StringUtils.CRLF);
            return result;
        }

        internal string query(string request)
        {
            Byte[] bytesSent = Encoding.ASCII.GetBytes(request);
            Byte[] bytesReceived = new Byte[256];

            socket.Send(bytesSent, bytesSent.Length, 0);

            int bytes = 0;
            string reply = "";
            do
            {
                bytes = socket.Receive(bytesReceived, bytesReceived.Length, 0);
                if (bytes > 0)
                {
                    string thisBatch = Encoding.ASCII.GetString(bytesReceived, 0, bytes);
                    reply += thisBatch;
                }
            }
            while (bytes == 256);
            return reply;
        }

    }
}
